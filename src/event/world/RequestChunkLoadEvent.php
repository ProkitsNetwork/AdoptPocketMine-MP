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

namespace pocketmine\event\world;

use pocketmine\world\format\io\LoadedChunkData;
use pocketmine\world\World;

class RequestChunkLoadEvent extends WorldEvent{
	private ?LoadedChunkData $chunk = null;
	private bool $set = false;

	public function __construct(World $world, private int $chunkX, private int $chunkZ){
		parent::__construct($world);
	}

	public function getChunkX() : int{ return $this->chunkX; }

	public function getChunkZ() : int{ return $this->chunkZ; }

	public function getChunkData() : ?LoadedChunkData{ return $this->chunk; }

	public function isSet() : bool{ return $this->set; }

	public function setChunkData(?LoadedChunkData $chunk) : void{
		$this->set = true;
		$this->chunk = $chunk;
	}
}
