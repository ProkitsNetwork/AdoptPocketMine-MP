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

namespace pocketmine\world\generator\overworld\populator\biome;

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\object\OreType;
use pocketmine\world\generator\object\OreVein;
use pocketmine\world\generator\overworld\populator\biome\utils\OreTypeHolder;
use pocketmine\world\generator\Populator;

class OrePopulator implements Populator{

	/** @var OreTypeHolder[] */
	private array $ores = [];

	/**
	 * Creates a populator for dirt, gravel, andesite, diorite, granite; and coal, iron, gold,
	 * redstone, diamond and lapis lazuli ores.
	 */
	public function __construct(){
		$this->addOre(new OreType(VanillaBlocks::DIRT(), 0, 256, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::GRAVEL(), 0, 256, 32), 8);
		$this->addOre(new OreType(VanillaBlocks::GRANITE(), 0, 80, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::DIORITE(), 0, 80, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::ANDESITE(), 0, 80, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::COAL_ORE(), 0, 128, 16), 20);
		$this->addOre(new OreType(VanillaBlocks::IRON_ORE(), 0, 64, 8), 20);
		$this->addOre(new OreType(VanillaBlocks::GOLD_ORE(), 0, 32, 8), 2);
		$this->addOre(new OreType(VanillaBlocks::REDSTONE_ORE(), 0, 16, 7), 8);
		$this->addOre(new OreType(VanillaBlocks::DIAMOND_ORE(), 0, 16, 7), 1);
		$this->addOre(new OreType(VanillaBlocks::LAPIS_LAZULI_ORE(), 16, 16, 6), 1);
	}

	protected function addOre(OreType $type, int $value) : void{
		$this->ores[] = new OreTypeHolder($type, $value);
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$cx = $chunk_x << Chunk::COORD_BIT_SIZE;
		$cz = $chunk_z << Chunk::COORD_BIT_SIZE;

		foreach($this->ores as $ore_type_holder){
			for($n = 0; $n < $ore_type_holder->value; ++$n){
				$source_x = $cx + $random->nextBoundedInt(16);
				$source_z = $cz + $random->nextBoundedInt(16);
				$source_y = $ore_type_holder->type->getRandomHeight($random);
				(new OreVein($ore_type_holder->type))->generate($world, $random, $source_x, $source_y, $source_z);
			}
		}
	}
}
