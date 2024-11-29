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
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Decorator;
use pocketmine\world\generator\object\Flower;
use pocketmine\world\generator\overworld\decorator\types\FlowerDecoration;

class FlowerDecorator extends Decorator{

	/**
	 * @param FlowerDecoration[] $decorations
	 */
	private static function getRandomFlower(Random $random, array $decorations) : ?Block{
		$total_weight = 0;
		foreach($decorations as $decoration){
			$total_weight += $decoration->weight;
		}

		if($total_weight > 0){
			$weight = $random->nextBoundedInt($total_weight);
			foreach($decorations as $decoration){
				$weight -= $decoration->weight;
				if($weight < 0){
					return $decoration->block;
				}
			}
		}

		return null;
	}

	/** @var FlowerDecoration[] */
	private array $flowers = [];

	final public function setFlowers(FlowerDecoration ...$flowers) : void{
		$this->flowers = $flowers;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$x = $random->nextBoundedInt(16);
		$z = $random->nextBoundedInt(16);
		$source_y = $random->nextBoundedInt($chunk->getHighestBlockAt($x & Chunk::COORD_MASK, $z & Chunk::COORD_MASK) + 32);

		// the flower can change on each decoration pass
		$flower = self::getRandomFlower($random, $this->flowers);
		if($flower !== null){
			(new Flower($flower))->generate($world, $random, ($chunk_x << Chunk::COORD_BIT_SIZE) + $x, $source_y, ($chunk_z << Chunk::COORD_BIT_SIZE) + $z);
		}
	}
}
