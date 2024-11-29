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
use pocketmine\world\generator\object\Cactus;

class CactusDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = $chunk_x << Chunk::COORD_BIT_SIZE;
		$source_z = $chunk_z << Chunk::COORD_BIT_SIZE;
		$x = $random->nextBoundedInt(16);
		$z = $random->nextBoundedInt(16);
		$sourceY = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) << 1);

		for($l = 0; $l < 10; ++$l){
			$i = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$k = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$j = $sourceY + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);
			(new Cactus())->generate($world, $random, $x + $i, $j, $z + $k);
		}
	}
}
