<?php

namespace pocketmine\event\world;

use pocketmine\world\format\io\LoadedChunkData;
use pocketmine\world\World;

class RequestChunkLoadEvent extends WorldEvent{
	private ?LoadedChunkData $chunk = null;

	public function __construct(World $world, private int $chunkX, private int $chunkZ,){
		parent::__construct($world);
	}

	public function getChunkX() : int{ return $this->chunkX; }

	public function getChunkZ() : int{ return $this->chunkZ; }

	public function getChunkData() : ?LoadedChunkData{
		return $this->chunk;
	}

	public function setChunkData(?LoadedChunkData $chunk) : void{
		$this->chunk = $chunk;
	}
}