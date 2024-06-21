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

use GlobalLogger;
use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use pocketmine\VersionInfo;
use Symfony\Component\Filesystem\Path;
use Throwable;
use function file_exists;
use function json_encode;
use function mkdir;
use function unlink;

/**
 * @internal
 */
class FilesystemCache{
	use SingletonTrait;

	private FilesystemMutex $mutex;

	private static function make() : self{
		$path = Path::join(DATA_PATH, "cache_data");
		return new self($path);
	}

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

	public function put(string $key, mixed $value) : void{
		$this->mutex->do(function() use ($value, $key){
			$file = Path::join($this->path, $this->toPathName($key));
			Filesystem::safeFilePutContents($file, ZlibCompressor::getInstance()->compress(igbinary_serialize($value)));
		});
	}

	public function has(string $key) : bool{
		return $this->mutex->do(function() use ($key){
			$path = Path::join($this->path, $this->toPathName($key));
			return file_exists($path);
		});
	}

	public function get(string $key) : mixed{
		return $this->mutex->do(function() use ($key){
			$file = Path::join($this->path, $this->toPathName($key));
			if(!file_exists($file)){
				return null;
			}
			try{
				return igbinary_unserialize(ZlibCompressor::getInstance()->decompress(Filesystem::fileGetContents($file)));
			}catch(Throwable $throwable){
				$logger = GlobalLogger::get();
				$logger->error("Data for $key is corrupted.");
				$logger->logException($throwable);
				$this->remove($key);
			}
			return null;
		});
	}

	public function remove(string $key) : void{
		$this->mutex->do(function() use ($key){
			$file = Path::join($this->path, $this->toPathName($key));
			if(file_exists($file)){
				Utils::assumeNotFalse(unlink($file), "Cache file cannot be removed");
			}
		});
	}

	private function toPathName(string $key) : string{
		return $key . '.dat';
	}
}