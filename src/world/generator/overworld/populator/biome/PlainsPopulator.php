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
use pocketmine\world\generator\object\DoubleTallPlant;
use pocketmine\world\generator\object\Flower;
use pocketmine\world\generator\object\TallGrass;
use pocketmine\world\generator\overworld\biome\BiomeIds;
use function count;

class PlainsPopulator extends BiomePopulator{

	/** @var Block[] */
	protected static array $PLAINS_FLOWERS;

	/** @var Block[] */
	protected static array $PLAINS_TULIPS;

	public static function init() : void{
		parent::init();

		self::$PLAINS_FLOWERS = [
			VanillaBlocks::POPPY(),
			VanillaBlocks::AZURE_BLUET(),
			VanillaBlocks::OXEYE_DAISY()
		];

		self::$PLAINS_TULIPS = [
			VanillaBlocks::RED_TULIP(),
			VanillaBlocks::ORANGE_TULIP(),
			VanillaBlocks::WHITE_TULIP(),
			VanillaBlocks::PINK_TULIP()
		];
	}

	private OctaveGenerator $noise_gen;

	public function __construct(){
		parent::__construct();
		$this->noise_gen = SimplexOctaveGenerator::fromRandomAndOctaves(new Random(2345), 1, 0, 0, 0);
		$this->noise_gen->setScale(1 / 200.0);
	}

	protected function initPopulators() : void{
		$this->flower_decorator->setAmount(0);
		$this->tall_grass_decorator->setAmount(0);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::PLAINS];
	}

	public function populateOnGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = $chunk_x << Chunk::COORD_BIT_SIZE;
		$source_z = $chunk_z << Chunk::COORD_BIT_SIZE;

		$flower_amount = 15;
		$tall_grass_amount = 5;
		if($this->noise_gen->noise($source_x + 8, $source_z + 8, 0, 0.5, 2.0, false) >= -0.8){
			$flower_amount = 4;
			$tall_grass_amount = 10;
			for($i = 0; $i < 7; ++$i){
				$x = $random->nextBoundedInt(16);
				$z = $random->nextBoundedInt(16);
				$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) + 32);
				(new DoubleTallPlant(VanillaBlocks::DOUBLE_TALLGRASS()))->generate($world, $random, $source_x + $x, $y, $source_z + $z);
			}
		}

		$flower = match(true){
			$this->noise_gen->noise($source_x + 8, $source_z + 8, 0, 0.5, 2.0, false) < -0.8 => self::$PLAINS_TULIPS[$random->nextBoundedInt(count(self::$PLAINS_TULIPS))],
			$random->nextBoundedInt(3) > 0 => self::$PLAINS_FLOWERS[$random->nextBoundedInt(count(self::$PLAINS_FLOWERS))],
			default => VanillaBlocks::DANDELION()
		};

		for($i = 0; $i < $flower_amount; ++$i){
			$x = $random->nextBoundedInt(16);
			$z = $random->nextBoundedInt(16);
			$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) + 32);
			(new Flower($flower))->generate($world, $random, $source_x + $x, $y, $source_z + $z);
		}

		for($i = 0; $i < $tall_grass_amount; ++$i){
			$x = $random->nextBoundedInt(16);
			$z = $random->nextBoundedInt(16);
			$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) << 1);
			(new TallGrass(VanillaBlocks::TALL_GRASS()))->generate($world, $random, $source_x + $x, $y, $source_z + $z);
		}

		parent::populateOnGround($world, $random, $chunk_x, $chunk_z, $chunk);
	}
}

PlainsPopulator::init();
