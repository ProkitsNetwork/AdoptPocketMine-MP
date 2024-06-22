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
use pocketmine\thread\Thread;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\io\FormatConverter;
use pocketmine\world\format\io\WorldProvider;
use pocketmine\world\format\io\WorldProviderManager;
use pocketmine\world\format\io\WritableWorldProvider;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\generator\InvalidGeneratorOptionsException;
use PrefixedLogger;
use Symfony\Component\Filesystem\Path;

class WorldProviderThread extends Thread{
	/** @var ThreadSafeArray */
	private ThreadSafeArray $loadQueue;
	/** @var ThreadSafeArray */
	private ThreadSafeArray $unloadQueue;
	private ThreadSafeArray $transactionQueue;

	private string $lang;

	use SingletonTrait;

	private static function make() : self{
		$self = new self(DATA_PATH);
		$self->start();
		return $self;
	}

	public function __construct(private string $dataPath){
		$this->lang = Server::getInstance()->getLanguage()->getLang();
		$this->loadQueue = new ThreadSafeArray();
		$this->unloadQueue = new ThreadSafeArray();
		$this->transactionQueue = new ThreadSafeArray();
	}

	private function getWorldPath(string $name) : string{
		return Path::join($this->dataPath, $name) . "/"; //TODO: check if we still need the trailing dirsep (I'm a little scared to remove it)
	}

	protected function loadWorldData(Language $lang, WorldProviderManager $providerManager, string $name, bool $autoUpgrade) : ?WorldProvider{
		if(trim($name) === ""){
			throw new InvalidArgumentException("Invalid empty world name");
		}
		if(isset($this->providers[$name])){
			return $this->providers[$name];
		}
		$path = $this->getWorldPath($name);
		if(!is_dir($path)){
			return null;
		}
		$providers = $providerManager->getMatchingProviders($path);
		if(count($providers) !== 1){
			GlobalLogger::get()->error($lang->translate(KnownTranslationFactory::pocketmine_level_loadError(
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
			GlobalLogger::get()->error($lang->translate(KnownTranslationFactory::pocketmine_level_loadError(
				$name,
				KnownTranslationFactory::pocketmine_level_corrupted($e->getMessage())
			)));
			return null;
		}catch(UnsupportedWorldFormatException $e){
			GlobalLogger::get()->error($lang->translate(KnownTranslationFactory::pocketmine_level_loadError(
				$name,
				KnownTranslationFactory::pocketmine_level_unsupportedFormat($e->getMessage())
			)));
			return null;
		}

		$generatorEntry = GeneratorManager::getInstance()->getGenerator($provider->getWorldData()->getGenerator());
		if($generatorEntry === null){
			GlobalLogger::get()->error($lang->translate(KnownTranslationFactory::pocketmine_level_loadError(
				$name,
				KnownTranslationFactory::pocketmine_level_unknownGenerator($provider->getWorldData()->getGenerator())
			)));
			return null;
		}
		try{
			$generatorEntry->validateGeneratorOptions($provider->getWorldData()->getGeneratorOptions());
		}catch(InvalidGeneratorOptionsException $e){
			GlobalLogger::get()->error($lang->translate(KnownTranslationFactory::pocketmine_level_loadError(
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
			GlobalLogger::get()->notice($lang->translate(KnownTranslationFactory::pocketmine_level_conversion_start($name)));

			$providerClass = $providerManager->getDefault();
			$converter = new FormatConverter($provider, $providerClass, Path::join(DATA_PATH, "backups", "worlds"), GlobalLogger::get());
			$converter->execute();
			$provider = $providerClass->fromPath($path, new PrefixedLogger(GlobalLogger::get(), "World Provider: $name"));

			GlobalLogger::get()->notice($lang->translate(KnownTranslationFactory::pocketmine_level_conversion_finish($name, $converter->getBackupPath())));
		}
		return $provider;
	}

	public function unloadWorld(ThreadSafeArray $providers, string $world) : void{
		if(!isset($providers[$world])){
			return;
		}
		$provider = $providers[$world];
		$provider->close();
		unset($providers[$world], $this->transactionQueue[$world]);
		GlobalLogger::get()->debug("Provider for world $world is unloaded.");
	}

	protected function onRun() : void{
		$lang = new Language($this->lang);
		$mgr = new WorldProviderManager();
		/** @var ThreadSafeArray|WorldProvider[] */
		$providers = new ThreadSafeArray();
		while(!$this->isKilled){
			while(($load = $this->loadQueue->pop()) !== null){
				/** @var FutureResolver<array{0:string,1:bool},void> $load */
				$load->do(function() use ($mgr, $lang, $load, &$providers){
					[$folder, $autoUpgrade] = $load->getContext();
					$provider = $this->loadWorldData($lang, $mgr, $folder, $autoUpgrade);
					if($provider === null){
						return null;
					}
					$providers[$folder] = $provider;
					$this->transactionQueue[$folder] = new ThreadSafeArray();
					return new BaseThreadedWorldProvider(
						$provider->getWorldMinY(),
						$provider->getWorldMaxY(),
						$this->getWorldPath($folder),
						$folder
					);
				});
			}
			foreach($this->unloadQueue as $idx => $world){
				if(empty($this->transactionQueue[$world])){
					$this->unloadWorld($providers, $world);
					unset($this->unloadQueue[$idx]);
				}
			}
			foreach($this->transactionQueue as $world => $queue){
				$provider = $providers[$world];
				if($provider === null){
					unset($this->transactionQueue[$world]);
					continue;
				}
				while(($resolver = $this->transactionQueue->pop()) !== null){
					assert($resolver instanceof FutureResolver);
					$resolver->do($resolver->getContext());
				}
			}
			usleep(1000);
		}
	}

	/**
	 * @param string $folderName
	 *
	 * @return Future<ThreadedWorldProvider|null>
	 */
	public function register(string $folderName, bool $autoUpgrade = true) : Future{
		$resolver = new FutureResolver([$folderName, $autoUpgrade]);
		$this->loadQueue->synchronized(fn() => $this->loadQueue[] = $resolver);
		return $resolver->future();
	}

	/**
	 * @param string $folderName
	 *
	 * @return Future<ThreadedWorldProvider>
	 */
	public function unregister(string $folderName) : Future{
		$resolver = new FutureResolver($folderName);
		$this->loadQueue->synchronized(fn() => $this->loadQueue[] = $resolver);
		return $resolver->future();
	}

	/**
	 * @template T
	 * @param Closure():T $c
	 *
	 * @return Future<T>|null
	 */
	public function transaction(string $world, \Closure $c) : ?Future{
		if(!isset($this->transactionQueue[$world])){
			return null;
		}
		$resolver = new FutureResolver($c);
		$this->transactionQueue[$world][] = $resolver;
		return $resolver->future();
	}
}
