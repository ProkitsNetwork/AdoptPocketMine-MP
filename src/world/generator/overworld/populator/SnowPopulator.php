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

namespace pocketmine\world\generator\overworld\populator;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\overworld\biome\BiomeClimateManager;
use pocketmine\world\generator\Populator;

class SnowPopulator implements Populator{

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = $chunk_x << Chunk::COORD_BIT_SIZE;
		$source_z = $chunk_z << Chunk::COORD_BIT_SIZE;

		$block_state_registry = RuntimeBlockStateRegistry::getInstance();
		$air = VanillaBlocks::AIR()->getStateId();
		$grass = VanillaBlocks::GRASS()->getStateId();
		$snow_layer = VanillaBlocks::SNOW_LAYER()->getStateId();

		$world_height = $world->getMaxY();

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$highest_y = $chunk->getHighestBlockAt($x, $z);
				if($highest_y > 0 && $highest_y < $world_height - 1){
					$y = $highest_y - 1;
					if(BiomeClimateManager::isSnowy($chunk->getBiomeId($x, 0, $z), $source_x + $x, $y, $source_z + $z)){
						switch($block_state_registry->fromStateId($chunk->getBlockStateId($x, $y, $z))->getTypeId()){
							case BlockTypeIds::WATER:
							case BlockTypeIds::SNOW:
							case BlockTypeIds::ICE:
							case BlockTypeIds::PACKED_ICE:
							case BlockTypeIds::DANDELION:
							case BlockTypeIds::POPPY:
							case BlockTypeIds::TALL_GRASS:
							case BlockTypeIds::DOUBLE_TALLGRASS:
							case BlockTypeIds::SUGARCANE:
							case BlockTypeIds::LAVA:
								break;
							case BlockTypeIds::DIRT:
								$chunk->setBlockStateId($x, $y, $z, $grass);
								if($chunk->getBlockStateId($x, $y + 1, $z) === $air){
									$chunk->setBlockStateId($x, $y + 1, $z, $snow_layer);
								}
								break;
							default:
								if($chunk->getBlockStateId($x, $y + 1, $z) === $air){
									$chunk->setBlockStateId($x, $y + 1, $z, $snow_layer);
								}
								break;
						}
					}
				}
			}
		}
	}
}
