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

use pocketmine\block\DoublePlant;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\object\DoubleTallPlant;
use pocketmine\world\generator\object\tree\BirchTree;
use pocketmine\world\generator\object\tree\GenericTree;
use pocketmine\world\generator\overworld\biome\BiomeIds;
use pocketmine\world\generator\overworld\decorator\types\TreeDecoration;
use function count;

class ForestPopulator extends BiomePopulator{

	private const BIOMES = [BiomeIds::FOREST, BiomeIds::FOREST_HILLS];

	/** @var TreeDecoration[] */
	protected static array $TREES;

	/** @var DoublePlant[] */
	private static array $DOUBLE_PLANTS;

	public static function init() : void{
		parent::init();
		self::$DOUBLE_PLANTS = [
			VanillaBlocks::LILAC(),
			VanillaBlocks::ROSE_BUSH(),
			VanillaBlocks::PEONY()
		];
	}

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(GenericTree::class, 4),
			new TreeDecoration(BirchTree::class, 1)
		];
	}

	protected int $double_plant_lowering_amount = 3;

	protected function initPopulators() : void{
		$this->double_plant_decorator->setAmount(0);
		$this->tree_decorator->setAmount(10);
		$this->tree_decorator->setTrees(...self::$TREES);
		$this->tall_grass_decorator->setAmount(2);
	}

	public function getBiomes() : ?array{
		return self::BIOMES;
	}

	public function populateOnGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = $chunk_x << Chunk::COORD_BIT_SIZE;
		$source_z = $chunk_z << Chunk::COORD_BIT_SIZE;
		$amount = $random->nextBoundedInt(5) - $this->double_plant_lowering_amount;
		$i = 0;
		while($i < $amount){
			for($j = 0; $j < 5; ++$j, ++$i){
				$x = $random->nextBoundedInt(16);
				$z = $random->nextBoundedInt(16);
				$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) + 32);
				$species = self::$DOUBLE_PLANTS[$random->nextBoundedInt(count(self::$DOUBLE_PLANTS))];
				if((new DoubleTallPlant($species))->generate($world, $random, $source_x + $x, $y, $source_z + $z)){
					++$i;
					break;
				}
			}
		}

		parent::populateOnGround($world, $random, $chunk_x, $chunk_z, $chunk);
	}
}

ForestPopulator::init();
