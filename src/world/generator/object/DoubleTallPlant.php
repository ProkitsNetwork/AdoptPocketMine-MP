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

namespace pocketmine\world\generator\object;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\DoublePlant;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class DoubleTallPlant extends TerrainObject{

	public function __construct(
		private DoublePlant $species
	){}

	/**
	 * Generates up to 64 plants around the given point.
	 *
	 * @return bool true whether least one plant was successfully generated
	 */
	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		$placed = false;
		$height = $world->getMaxY();
		for($i = 0; $i < 64; ++$i){
			$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			$block = $world->getBlockAt($x, $y, $z);
			$top_block = $world->getBlockAt($x, $y + 1, $z);
			if($y < $height && $block->getTypeId() === BlockTypeIds::AIR && $top_block->getTypeId() === BlockTypeIds::AIR && $world->getBlockAt($x, $y - 1, $z)->getTypeId() === BlockTypeIds::GRASS){
				$world->setBlockAt($x, $y, $z, $this->species->setTop(false));
				$world->setBlockAt($x, $y + 1, $z, $this->species->setTop(true));
				$placed = true;
			}
		}

		return $placed;
	}
}
