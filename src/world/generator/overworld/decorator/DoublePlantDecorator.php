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

use pocketmine\block\DoublePlant;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Decorator;
use pocketmine\world\generator\object\DoubleTallPlant;
use pocketmine\world\generator\overworld\decorator\types\DoublePlantDecoration;

class DoublePlantDecorator extends Decorator{

	/**
	 * @param DoublePlantDecoration[] $decorations
	 */
	private static function getRandomDoublePlant(Random $random, array $decorations) : ?DoublePlant{
		$totalWeight = 0;
		foreach($decorations as $decoration){
			$totalWeight += $decoration->weight;
		}
		$weight = $random->nextBoundedInt($totalWeight);
		foreach($decorations as $decoration){
			$weight -= $decoration->weight;
			if($weight < 0){
				return $decoration->block;
			}
		}
		return null;
	}

	/** @var DoublePlantDecoration[] */
	private array $doublePlants = [];

	final public function setDoublePlants(DoublePlantDecoration ...$doublePlants) : void{
		$this->doublePlants = $doublePlants;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$x = $random->nextBoundedInt(16);
		$z = $random->nextBoundedInt(16);
		$source_y = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) + 32);

		$species = self::getRandomDoublePlant($random, $this->doublePlants);
		(new DoubleTallPlant($species))->generate($world, $random, ($chunk_x << Chunk::COORD_BIT_SIZE) + $x, $source_y, ($chunk_z << Chunk::COORD_BIT_SIZE) + $z);
	}
}
