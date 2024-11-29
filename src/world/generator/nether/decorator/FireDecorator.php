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

namespace pocketmine\world\generator\nether\decorator;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Decorator;

class FireDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$amount = 1 + $random->nextBoundedInt(1 + $random->nextBoundedInt(10));

		$height = $world->getMaxY();
		$source_y_margin = 8 * ($height >> 7);

		for($j = 0; $j < $amount; ++$j){
			$source_x = ($chunk_x << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
			$source_z = ($chunk_z << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
			$source_y = 4 + $random->nextBoundedInt($source_y_margin);

			for($i = 0; $i < 64; ++$i){
				$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

				$block = $world->getBlockAt($x, $y, $z);
				$block_below = $world->getBlockAt($x, $y - 1, $z);
				if(
					$y < $height &&
					$block->getTypeId() === BlockTypeIds::AIR &&
					$block_below->getTypeId() === BlockTypeIds::NETHERRACK
				){
					$world->setBlockAt($x, $y, $z, VanillaBlocks::FIRE());
				}
			}
		}
	}
}
