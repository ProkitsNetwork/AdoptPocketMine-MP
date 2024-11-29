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
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Decorator;

class GlowstoneDecorator extends Decorator{

	private const SIDES = [Facing::EAST, Facing::WEST, Facing::DOWN, Facing::UP, Facing::SOUTH, Facing::NORTH];

	public function __construct(
		private bool $variable_amount = false
	){}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$amount = $this->variable_amount ? 1 + $random->nextBoundedInt(1 + $random->nextBoundedInt(10)) : 10;

		$height = $world->getMaxY();
		$source_y_margin = 8 * ($height >> 7);

		for($i = 0; $i < $amount; ++$i){
			$source_x = ($chunk_x << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
			$source_z = ($chunk_z << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
			$source_y = 4 + $random->nextBoundedInt($height - $source_y_margin);

			$block = $world->getBlockAt($source_x, $source_y, $source_z);
			if(
				$block->getTypeId() !== BlockTypeIds::AIR ||
				$world->getBlockAt($source_x, $source_y + 1, $source_z)->getTypeId() !== BlockTypeIds::NETHERRACK
			){
				continue;
			}

			$world->setBlockAt($source_x, $source_y, $source_z, VanillaBlocks::GLOWSTONE());

			for($j = 0; $j < 1500; ++$j){
				$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$y = $source_y - $random->nextBoundedInt(12);
				$block = $world->getBlockAt($x, $y, $z);
				if($block->getTypeId() !== BlockTypeIds::AIR){
					continue;
				}

				$glowstone_block_count = 0;
				$vector = new Vector3($x, $y, $z);
				foreach(self::SIDES as $face){
					$pos = $vector->getSide($face);
					if($world->getBlockAt($pos->x, $pos->y, $pos->z)->getTypeId() === BlockTypeIds::GLOWSTONE){
						++$glowstone_block_count;
					}
				}

				if($glowstone_block_count === 1){
					$world->setBlockAt($x, $y, $z, VanillaBlocks::GLOWSTONE());
				}
			}
		}
	}
}
