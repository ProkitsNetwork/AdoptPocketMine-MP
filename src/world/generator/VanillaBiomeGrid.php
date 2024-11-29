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

namespace pocketmine\world\generator;

use pocketmine\world\format\Chunk;
use pocketmine\world\generator\biomegrid\BiomeGrid;
use function array_key_exists;

class VanillaBiomeGrid implements BiomeGrid{

	/** @var int[] */
	public array $biomes = [];

	public function getBiome(int $x, int $z) : ?int{
		// upcasting is very important to get extended biomes
		return array_key_exists($hash = $x | $z << Chunk::COORD_BIT_SIZE, $this->biomes) ? $this->biomes[$hash] & 0xFF : null;
	}

	public function setBiome(int $x, int $z, int $biome_id) : void{
		$this->biomes[$x | $z << Chunk::COORD_BIT_SIZE] = $biome_id;
	}
}
