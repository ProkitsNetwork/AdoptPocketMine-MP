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

namespace pocketmine\world\generator\overworld\decorator;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Dirt;
use pocketmine\block\utils\DirtType;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Decorator;
use function assert;

class MushroomDecorator extends Decorator{

	private bool $fixed_height_range = false;
	private float $density = 0.0;

	public function __construct(
		private Block $type
	){}

	public function setUseFixedHeightRange() : MushroomDecorator{
		$this->fixed_height_range = true;
		return $this;
	}

	public function setDensity(float $density) : MushroomDecorator{
		$this->density = $density;
		return $this;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		if($random->nextFloat() < $this->density){
			$source_x = ($chunk_x << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
			$source_z = ($chunk_z << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
			$source_y = $chunk->getHighestBlockAt($source_x & Chunk::COORD_MASK, $source_z & Chunk::COORD_MASK);
			$source_y = $this->fixed_height_range ? $source_y : $random->nextBoundedInt($source_y << 1);

			$height = $world->getMaxY();
			for($i = 0; $i < 64; ++$i){
				$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

				$block = $world->getBlockAt($x, $y, $z);
				$below_below = $world->getBlockAt($x, $y - 1, $z);
				if($y < $height && $block->getTypeId() === BlockTypeIds::AIR){
					switch($below_below->getTypeId()){
						case BlockTypeIds::MYCELIUM:
						case BlockTypeIds::PODZOL:
							$can_place_shroom = true;
							break;
						case BlockTypeIds::GRASS:
							$can_place_shroom = ($block->getLightLevel() < 13);
							break;
						case BlockTypeIds::DIRT:
							assert($below_below instanceof Dirt);
							if($below_below->getDirtType() !== DirtType::COARSE){
								$can_place_shroom = $block->getLightLevel() < 13;
							}else{
								$can_place_shroom = false;
							}
							break;
						default:
							$can_place_shroom = false;
					}
					if($can_place_shroom){
						$world->setBlockAt($x, $y, $z, $this->type);
					}
				}
			}
		}
	}
}
