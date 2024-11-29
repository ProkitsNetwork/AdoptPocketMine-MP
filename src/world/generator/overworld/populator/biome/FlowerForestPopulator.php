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

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\noise\bukkit\OctaveGenerator;
use pocketmine\world\generator\noise\glowstone\SimplexOctaveGenerator;
use pocketmine\world\generator\object\Flower;
use pocketmine\world\generator\overworld\biome\BiomeIds;
use function count;

class FlowerForestPopulator extends ForestPopulator{

	/** @var Block[] */
	private static array $FOREST_FLOWERS;

	protected static function initFlowers() : void{
		self::$FOREST_FLOWERS = [
			VanillaBlocks::POPPY(),
			VanillaBlocks::POPPY(),
			VanillaBlocks::DANDELION(),
			VanillaBlocks::ALLIUM(),
			VanillaBlocks::AZURE_BLUET(),
			VanillaBlocks::RED_TULIP(),
			VanillaBlocks::ORANGE_TULIP(),
			VanillaBlocks::WHITE_TULIP(),
			VanillaBlocks::PINK_TULIP(),
			VanillaBlocks::OXEYE_DAISY()
		];
	}

	private OctaveGenerator $noise_gen;

	protected function initPopulators() : void{
		parent::initPopulators();
		$this->tree_decorator->setAmount(6);
		$this->flower_decorator->setAmount(0);
		$this->double_plant_lowering_amount = 1;
		$this->noise_gen = SimplexOctaveGenerator::fromRandomAndOctaves(new Random(2345), 1, 0, 0, 0);
		$this->noise_gen->setScale(1 / 48.0);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::FLOWER_FOREST];
	}

	public function populateOnGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		parent::populateOnGround($world, $random, $chunk_x, $chunk_z, $chunk);

		$source_x = $chunk_x << Chunk::COORD_BIT_SIZE;
		$source_z = $chunk_z << Chunk::COORD_BIT_SIZE;

		for($i = 0; $i < 100; ++$i){
			$x = $random->nextBoundedInt(16);
			$z = $random->nextBoundedInt(16);
			$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) + 32);
			$noise = ($this->noise_gen->noise($x, $z, 0.5, 0, 2.0, false) + 1.0) / 2.0;
			$noise = $noise < 0 ? 0 : ($noise > 0.9999 ? 0.9999 : $noise);
			$flower = self::$FOREST_FLOWERS[(int) ($noise * count(self::$FOREST_FLOWERS))];
			(new Flower($flower))->generate($world, $random, $source_x + $x, $y, $source_z + $z);
		}
	}
}

FlowerForestPopulator::init();
