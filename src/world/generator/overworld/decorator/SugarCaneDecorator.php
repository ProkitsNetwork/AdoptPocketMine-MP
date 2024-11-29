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

use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Decorator;
use pocketmine\world\generator\object\SugarCane;

class SugarCaneDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = ($chunk_x << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
		$source_z = ($chunk_z << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
		$max_y = $chunk->getHighestBlockAt($source_x & Chunk::COORD_MASK, $source_z & Chunk::COORD_MASK);
		if($max_y <= 0){
			return;
		}
		$source_y = $random->nextBoundedInt($max_y << 1);
		for($j = 0; $j < 20; ++$j){
			$x = $source_x + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);
			$z = $source_z + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);
			(new SugarCane())->generate($world, $random, $x, $source_y, $z);
		}
	}
}
