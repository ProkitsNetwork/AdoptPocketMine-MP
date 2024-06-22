<?php

namespace pocketmine\world\format;

use pocketmine\promise\Future;
use pocketmine\world\format\io\ChunkData;
use pocketmine\world\format\io\WorldData;
use pocketmine\world\format\io\WorldProvider;
use pocketmine\world\format\io\WritableWorldProvider;

class BaseThreadedWorldProvider implements ThreadedWorldProvider{
	public function __construct(
		private int $minY,
		private int $maxY,
		private string $path,
		private string $world
	){

	}

	public function getWorldMinY() : int{
		return $this->minY;
	}

	public function getWorldMaxY() : int{
		return $this->maxY;
	}

	/*
		public function getPath() : string{
			return $this->path;
		}
	*/

	public function loadChunk(int $chunkX, int $chunkZ) : Future{
		return WorldProviderThread::getInstance()->transaction($this->world, static function(WorldProvider $provider) use ($chunkZ, $chunkX){
			return $provider->loadChunk($chunkX, $chunkZ);
		});
	}

	public function getWorldData() : Future{
		return WorldProviderThread::getInstance()->transaction($this->world, static function(WorldProvider $provider){
			return $provider->getWorldData();
		});
	}

	public function saveWorldData(WorldData $worldData) : Future{
		$worldData->save();
		return WorldProviderThread::getInstance()->transaction($this->world, static function(WorldProvider $provider){
			$provider->reloadWorldData();
		});
	}

	public function calculateChunkCount() : Future{
		return WorldProviderThread::getInstance()->transaction($this->world, static function(WorldProvider $provider){
			return $provider->calculateChunkCount();
		});
	}

	/**
	 * Saves a chunk (usually to disk).
	 */
	public function saveChunk(int $chunkX, int $chunkZ, ChunkData $chunkData, int $dirtyFlags) : Future{
		$chunkData = igbinary_serialize($chunkData);
		return WorldProviderThread::getInstance()->transaction($this->world, static function(WorldProvider $provider) use ($chunkZ, $chunkX, $chunkData, $dirtyFlags){
			if($provider instanceof WritableWorldProvider){
				$provider->saveChunk($chunkX, $chunkZ, igbinary_unserialize($chunkData), $dirtyFlags);
			}else{
				throw new \RuntimeException("not saved");
			}
		});
	}

	public function reloadWorldData() : Future{
		return WorldProviderThread::getInstance()->transaction($this->world, static function(WorldProvider $provider){
			$provider->reloadWorldData();
		});
	}

	public function doGarbageCollection() : Future{
		return WorldProviderThread::getInstance()->transaction($this->world, static function(WorldProvider $provider){
			$provider->doGarbageCollection();
		});
	}
}