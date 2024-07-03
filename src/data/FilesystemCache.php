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

namespace pocketmine\data;

use Closure;
use GlobalLogger;
use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use pocketmine\VersionInfo;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Throwable;
use function defined;
use function file_exists;
use function igbinary_serialize;
use function igbinary_unserialize;
use function is_dir;
use function json_encode;
use function mkdir;
use function unlink;
use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
class FilesystemCache{
	use SingletonTrait;

	private FilesystemMutex $mutex;

	public function __construct(private string $path){
		if(!is_dir($this->path)){
			Utils::assumeNotFalse(mkdir($this->path, 0777));
		}
		$this->mutex = new FilesystemMutex(Path::join($this->path, '.lock'));
		$this->mutex->do(function(){
			$version = json_encode([
				VersionInfo::VERSION(),
				VersionInfo::GIT_HASH(),
				VersionInfo::BUILD_NUMBER(),
				ProtocolInfo::MINECRAFT_VERSION,
				ProtocolInfo::CURRENT_PROTOCOL,
				ProtocolInfo::ACCEPTED_PROTOCOL
			], JSON_THROW_ON_ERROR);
			if($this->get('.metadata') !== $version){
				GlobalLogger::get()->debug("Cached data has expired.");
				Filesystem::recursiveUnlink($this->path);
				Utils::assumeNotFalse(mkdir($this->path, 0777));
			}
			$this->put('.metadata', $version);
			Utils::assumeNotFalse(is_dir($this->path), "Cache directory cannot be created");
		});
	}

	public function get(string $key) : mixed{
		return $this->mutex->do(fn() => $this->getValInternal($key));
	}

	private function getValInternal(string $key) : mixed{
		$file = Path::join($this->path, $this->toPathName($key));
		if(!file_exists($file)){
			return null;
		}
		try{
			$decompressed = ZlibCompressor::getInstance()->decompress(Filesystem::fileGetContents($file));
			if($decompressed === ''){
				return null;
			}
			return igbinary_unserialize($decompressed);
		}catch(Throwable $throwable){
			$logger = GlobalLogger::get();
			$logger->error("Data for $key is corrupted.");
			$logger->logException($throwable);
			$this->remove($key);
		}
		return null;
	}

	private function toPathName(string $key) : string{
		return $key . '.dat';
	}

	public function remove(string $key) : void{
		$this->mutex->do(function() use ($key){
			$file = Path::join($this->path, $this->toPathName($key));
			if(file_exists($file)){
				Utils::assumeNotFalse(unlink($file), "Cache file cannot be removed");
			}
		});
	}

	public function put(string $key, mixed $value) : void{
		$this->mutex->do(function() use ($value, $key){
			$file = Path::join($this->path, $this->toPathName($key));
			$this->putValInternal($file, $value);
		});
	}

	private function putValInternal(string $file, mixed $value) : void{
		$serialized = igbinary_serialize($value) ?? '';
		$compressed = ZlibCompressor::getInstance()->compress($serialized);
		Filesystem::safeFilePutContents($file, $compressed);
	}

	private static function make() : self{
		if(!defined("DATA_PATH")){
			throw new RuntimeException("Data path is not defined");
		}
		$path = Path::join(DATA_PATH, "cache_data");
		return new self($path);
	}

	/**
	 * @template T
	 * @param Closure():T              $fn
	 * @param null|Closure(mixed):bool $validator
	 *
	 * @return T
	 */
	public function getOrDefault(string $key, Closure $fn, ?Closure $validator = null){
		return $this->mutex->do(function() use ($validator, $key, $fn){
			$path = Path::join($this->path, $this->toPathName($key));
			if(!file_exists($path)){
				write_default:
				$v = $fn();
				if($validator !== null){
					Utils::assumeNotFalse($validator($v));
				}
				$this->putValInternal($path, $v);
				return $v;
			}
			$val = $this->getValInternal($path);
			if($validator !== null && !$validator($val)){
				goto write_default;
			}
			return $val;
		});
	}

	public function has(string $key) : bool{
		return $this->mutex->do(function() use ($key) : bool{
			$path = Path::join($this->path, $this->toPathName($key));
			return file_exists($path);
		});
	}
}
