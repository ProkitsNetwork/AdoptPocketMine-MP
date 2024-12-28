<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\world\format;

use GlobalLogger;
use InvalidArgumentException;
use pmmp\thread\ThreadSafeArray;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Language;
use pocketmine\promise\Future;
use pocketmine\promise\FutureResolver;
use pocketmine\Server;
use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\thread\Thread;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\io\FormatConverter;
use pocketmine\world\format\io\WorldProvider;
use pocketmine\world\format\io\WorldProviderManager;
use pocketmine\world\format\io\WritableWorldProvider;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\generator\InvalidGeneratorOptionsException;
use PrefixedLogger;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Throwable;
use function array_keys;
use function array_shift;
use function assert;
use function count;
use function igbinary_serialize;
use function igbinary_unserialize;
use function implode;
use function is_callable;
use function is_dir;
use function is_string;
use function iterator_to_array;
use function trim;

class WorldProviderThread extends Thread{
	private ThreadSafeArray $loadQueue;
	private ThreadSafeArray $unloadQueue;
	private ThreadSafeArray $transactionQueue;
	private ThreadSafeArray $executeQueue;

	private string $lang;
	private ThreadSafeLogger $logger;

	use SingletonTrait;

	private static function make() : self{
		$self = new self(DATA_PATH);
		$self->start();
		return $self;
	}

	public function __construct(private string $dataPath){
		$this->lang = igbinary_serialize(Server::getInstance()->getLanguage());
		$this->loadQueue = new ThreadSafeArray();
		$this->unloadQueue = new ThreadSafeArray();
		$this->transactionQueue = new ThreadSafeArray();
		$this->executeQueue = new ThreadSafeArray();
		$this->logger = Server::getInstance()->getLogger();
	}

	private function getWorldPath(string $name) : string{
		return Path::join($this->dataPath, "worlds", $name) . "/"; //TODO: check if we still need the trailing dirsep (I'm a little scared to remove it)
	}

	protected function loadWorldData(Language $lang, WorldProviderManager $providerManager, string $name, bool $autoUpgrade) : ?WorldProvider{
		if(trim($name) === ""){
			throw new InvalidArgumentException("Invalid empty world name");
		}
		$path = $this->getWorldPath($name);
		if(!is_dir($path)){
			return null;
		}
		$providers = $providerManager->getMatchingProviders($path);
		if(count($providers) !== 1){
			$this->logger->error($lang->translate(KnownTranslationFactory::pocketmine_level_loadError(
				$name,
				count($providers) === 0 ?
					KnownTranslationFactory::pocketmine_level_unknownFormat() :
					KnownTranslationFactory::pocketmine_level_ambiguousFormat(implode(", ", array_keys($providers)))
			)));
			return null;
		}
		$providerClass = array_shift($providers);

		try{
			$provider = $providerClass->fromPath($path, new PrefixedLogger(GlobalLogger::get(), "World Provider: $name"));
		}catch(CorruptedWorldException $e){
			$this->logger->error($lang->translate(KnownTranslationFactory::pocketmine_level_loadError(
				$name,
				KnownTranslationFactory::pocketmine_level_corrupted($e->getMessage())
			)));
			return null;
		}catch(UnsupportedWorldFormatException $e){
			$this->logger->error($lang->translate(KnownTranslationFactory::pocketmine_level_loadError(
				$name,
				KnownTranslationFactory::pocketmine_level_unsupportedFormat($e->getMessage())
			)));
			return null;
		}

		$generatorEntry = GeneratorManager::getInstance()->getGenerator($provider->getWorldData()->getGenerator());
		if($generatorEntry === null){
			$this->logger->error($lang->translate(KnownTranslationFactory::pocketmine_level_loadError(
				$name,
				KnownTranslationFactory::pocketmine_level_unknownGenerator($provider->getWorldData()->getGenerator())
			)));
			return null;
		}
		try{
			$generatorEntry->validateGeneratorOptions($provider->getWorldData()->getGeneratorOptions());
		}catch(InvalidGeneratorOptionsException $e){
			$this->logger->error($lang->translate(KnownTranslationFactory::pocketmine_level_loadError(
				$name,
				KnownTranslationFactory::pocketmine_level_invalidGeneratorOptions(
					$provider->getWorldData()->getGeneratorOptions(),
					$provider->getWorldData()->getGenerator(),
					$e->getMessage()
				)
			)));
			return null;
		}
		if(!($provider instanceof WritableWorldProvider)){
			if(!$autoUpgrade){
				throw new UnsupportedWorldFormatException("World \"$name\" is in an unsupported format and needs to be upgraded");
			}
			$this->logger->notice($lang->translate(KnownTranslationFactory::pocketmine_level_conversion_start($name)));

			$providerClass = $providerManager->getDefault();
			$converter = new FormatConverter($provider, $providerClass, Path::join(DATA_PATH, "backups", "worlds"), GlobalLogger::get());
			$converter->execute();
			$provider = $providerClass->fromPath($path, new PrefixedLogger(GlobalLogger::get(), "World Provider: $name"));

			$this->logger->notice($lang->translate(KnownTranslationFactory::pocketmine_level_conversion_finish($name, $converter->getBackupPath())));
		}
		return $provider;
	}

	protected function onRun() : void{
		GlobalLogger::set($this->logger);
		/** @var WorldProvider[] $providers */
		$providers = [];
		$lang = igbinary_unserialize($this->lang);
		$mgr = new WorldProviderManager();

		while(!$this->isKilled){
			try{
				while(is_callable($func = $this->executeQueue->pop())){
					$func();
				}
				while(($resolver = $this->lockedShift($this->loadQueue)) !== null){
					/** @var FutureResolver<array{0:string,1:bool},void> $resolver */
					if($resolver->isCancelled()){
						continue;
					}

					$resolver->do(function() use ($mgr, $lang, $resolver, &$providers){
						[$folderName, $autoUpgrade] = $resolver->getContext();
						try{
							if(isset($providers[$folderName])){
								throw new RuntimeException("Provider for \"$folderName\" has already loaded.");
							}

							$provider = $this->loadWorldData($lang, $mgr, $folderName, $autoUpgrade);
							$this->logger->debug("World provider for \"$folderName\" is loaded.");

							if($provider === null){
								return null;
							}

							$providers[$folderName] = $provider;
							$this->transactionQueue->synchronized(fn() => $this->transactionQueue[$folderName] = new ThreadSafeArray());

							return new BaseThreadedWorldProvider(
								$provider->getWorldMinY(),
								$provider->getWorldMaxY(),
								$this->getWorldPath($folderName),
								$folderName
							);
						}catch(Throwable $e){
							$this->logger->critical("Failed to load \"$folderName\".");
							$this->logger->logException($e);
							return null;
						}
					});
				}

				foreach(Utils::promoteKeys($this->lockedGetAll($this->unloadQueue)) as $idx => $resolver){
					assert($resolver instanceof FutureResolver);
					if($resolver->isCancelled()){
						continue;
					}

					$folderName = $resolver->getContext();
					assert(is_string($folderName));

					if(!$this->isKilled && !empty($this->transactionQueue->synchronized(fn() => $this->transactionQueue[$folderName] ?? []))){
						if(!isset($providers[$folderName])){
							continue;
						}

						$resolver->do(function() use (&$providers, $folderName){
							try{
								$provider = $providers[$folderName];
								$provider->close();
								$this->logger->debug("World provider for \"$folderName\" is unloaded.");

								$this->transactionQueue->synchronized(function() use ($folderName){
									unset($this->transactionQueue[$folderName]);
								});

								unset($providers[$folderName]);
							}catch(Throwable $e){
								$this->logger->critical("Failed to unload world provider for \"$folderName\".");
								$this->logger->logException($e);
							}
						});
					}
				}

				foreach(Utils::promoteKeys($this->lockedGetAll($this->transactionQueue)) as $world => $queue){
					$provider = $providers[$world] ?? null;
					if($provider === null){
						$this->transactionQueue->synchronized(function() use ($world){ unset($this->transactionQueue[$world]); });
						continue;
					}

					while(($resolver = $this->lockedShift($queue)) !== null){
						if($resolver->isCancelled()){
							continue;
						}

						assert($resolver instanceof FutureResolver);
						try{
							$resolver->do(static fn() => $resolver->getContext()($provider));
						}catch(Throwable $e){
							$this->logger->critical("Failed to execute transaction for $world.");
							$this->logger->logException($e);
						}
					}
				}

				$need = $this->transactionQueue->synchronized(function(){
					foreach($this->transactionQueue as $queue){
						if(count($queue) !== 0){
							return true;
						}
					}
					return false;
				});

				if(!$need && $this->unloadQueue->synchronized(fn() => empty($this->unloadQueue))){
					$this->synchronized(function() : void{
						if(!$this->isKilled){
							$this->wait(1000);
						}
					});
				}
			}catch(Throwable $e){
				$this->logger->critical("Unhandled exception in onRun loop.");
				$this->logger->logException($e);
			}
		}

		foreach(Utils::promoteKeys($providers) as $folderName => $provider){
			try{
				$provider->close();
				$this->logger->debug("Closed world provider for \"$folderName\" on shutdown gracefully.");
			}catch(Throwable $e){
				$this->logger->critical("Error closing provider for \"$folderName\" during shutdown.");
				$this->logger->logException($e);
			}
		}
	}

	private function lockedShift(ThreadSafeArray $queue) : ?FutureResolver{
		return $queue->synchronized(fn() => $queue->shift());
	}

	private function lockedGetAll(ThreadSafeArray $queue) : array{
		return $queue->synchronized(fn() => iterator_to_array($queue));
	}

	/**
	 * @return Future<ThreadedWorldProvider|null>
	 */
	public function register(string $folderName, bool $autoUpgrade = true) : Future{
		$resolver = new FutureResolver([$folderName, $autoUpgrade]);
		$this->logger->debug("Registering world provider for $folderName.");
		$this->loadQueue->synchronized(fn() => $this->loadQueue[] = $resolver);
		$this->synchronized(function() : void{
			$this->notify();
		});
		return $resolver->future();
	}

	/**
	 * @return Future<ThreadedWorldProvider>
	 */
	public function unregister(string $folderName) : Future{
		$resolver = new FutureResolver($folderName);
		$this->logger->debug("Unregistering world provider for $folderName.");
		$this->unloadQueue->synchronized(fn() => $this->unloadQueue[] = $resolver);
		$this->synchronized(function() : void{
			$this->notify();
		});
		return $resolver->future();
	}

	/**
	 * @template T
	 * @param Closure():T $c
	 *
	 * @return Future<T>|null
	 */
	public function transaction(string $world, \Closure $c) : ?Future{
		return $this->transactionQueue->synchronized(function() use ($world, $c){
			if(!isset($this->transactionQueue[$world])){
				return null;
			}
			$resolver = new FutureResolver($c);
			$this->transactionQueue[$world][] = $resolver;
			$this->synchronized(function() : void{
				$this->notify();
			});
			return $resolver->future();
		});
	}

	public function execute(\Closure $closure) : void{
		$this->executeQueue->synchronized(fn() => $this->executeQueue[] = $closure);
	}
}
