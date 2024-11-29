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
use pocketmine\block\Dirt;
use pocketmine\block\utils\DirtType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class SugarCane extends TerrainObject{

	private const FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($world->getBlockAt($source_x, $source_y, $source_z)->getTypeId() !== BlockTypeIds::AIR){
			return false;
		}

		$vec = new Vector3($source_x, $source_y - 1, $source_z);
		$adjacent_water = false;
		foreach(self::FACES as $face){
			// needs a directly adjacent water block
			$block_type_v = $vec->getSide($face);
			$block_type = $world->getBlockAt($block_type_v->x, $block_type_v->y, $block_type_v->z);
			if($block_type instanceof Water){
				$adjacent_water = true;
				break;
			}
		}
		if(!$adjacent_water){
			return false;
		}
		for($n = 0; $n <= $random->nextBoundedInt($random->nextBoundedInt(3) + 1) + 1; ++$n){
			$block = $world->getBlockAt($source_x, $source_y + $n - 1, $source_z);
			$block_id = $block->getTypeId();
			if($block_id === BlockTypeIds::SUGARCANE
				|| $block_id === BlockTypeIds::GRASS
				|| $block_id === BlockTypeIds::SAND
				|| ($block instanceof Dirt && $block->getDirtType()->equals(DirtType::NORMAL))
			){
				$cane_block = $world->getBlockAt($source_x, $source_y + $n, $source_z);
				if($cane_block->getTypeId() !== BlockTypeIds::AIR && $world->getBlockAt($source_x, $source_y + $n + 1, $source_z)->getTypeId() !== BlockTypeIds::AIR){
					return $n > 0;
				}

				$world->setBlockAt($source_x, $source_y + $n, $source_z, VanillaBlocks::SUGARCANE());
			}
		}
		return true;
	}
}
