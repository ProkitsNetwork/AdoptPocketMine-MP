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
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class Cactus extends TerrainObject{

	private const FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	/**
	 * Generates or extends a cactus, if there is space.
	 */
	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($world->getBlockAt($source_x, $source_y, $source_z)->getTypeId() === BlockTypeIds::AIR){
			$height = $random->nextBoundedInt($random->nextBoundedInt(3) + 1) + 1;
			for($n = $source_y; $n < $source_y + $height; ++$n){
				$vec = new Vector3($source_x, $n, $source_z);
				$type_below = $world->getBlockAt($source_x, $n - 1, $source_z)->getTypeId();
				if(($type_below === BlockTypeIds::SAND || $type_below === BlockTypeIds::CACTUS) && $world->getBlockAt($source_x, $n + 1, $source_z)->getTypeId() === BlockTypeIds::AIR){
					foreach(self::FACES as $face){
						$face = $vec->getSide($face);
						if($world->getBlockAt($face->x, $face->y, $face->z)->isSolid()){
							return $n > $source_y;
						}
					}

					$world->setBlockAt($source_x, $n, $source_z, VanillaBlocks::CACTUS());
				}
			}
		}
		return true;
	}
}
