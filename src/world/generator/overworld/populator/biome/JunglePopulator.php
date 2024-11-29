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

use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\object\tree\BigOakTree;
use pocketmine\world\generator\object\tree\CocoaTree;
use pocketmine\world\generator\object\tree\JungleBush;
use pocketmine\world\generator\object\tree\MegaJungleTree;
use pocketmine\world\generator\overworld\biome\BiomeIds;
use pocketmine\world\generator\overworld\decorator\MelonDecorator;
use pocketmine\world\generator\overworld\decorator\types\TreeDecoration;

class JunglePopulator extends BiomePopulator{

	/** @var TreeDecoration[] */
	protected static array $TREES;

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(BigOakTree::class, 10),
			new TreeDecoration(JungleBush::class, 50),
			new TreeDecoration(MegaJungleTree::class, 15),
			new TreeDecoration(CocoaTree::class, 30)
		];
	}

	protected MelonDecorator $melon_decorator;

	public function __construct(){
		$this->melon_decorator = new MelonDecorator();
		parent::__construct();
	}

	protected function initPopulators() : void{
		$this->tree_decorator->setAmount(65);
		$this->tree_decorator->setTrees(...self::$TREES);
		$this->flower_decorator->setAmount(4);
		$this->flower_decorator->setFlowers(...self::$FLOWERS);
		$this->tall_grass_decorator->setAmount(25);
		$this->tall_grass_decorator->setFernDensity(0.25);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::JUNGLE, BiomeIds::JUNGLE_HILLS, BiomeIds::JUNGLE_MUTATED];
	}

	protected function populateOnGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = $chunk_x << Chunk::COORD_BIT_SIZE;
		$source_z = $chunk_z << Chunk::COORD_BIT_SIZE;

		for($i = 0; $i < 7; ++$i){
			$x = $random->nextBoundedInt(16);
			$z = $random->nextBoundedInt(16);
			$y = $chunk->getHighestBlockAt($x, $z);
			$delegate = new BlockTransaction($world);
			$bush = new JungleBush($random, $delegate);
			if($bush->generate($world, $random, $source_x + $x, $y, $source_z + $z)){
				$delegate->apply();
			}
		}

		parent::populateOnGround($world, $random, $chunk_x, $chunk_z, $chunk);
		$this->melon_decorator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
	}
}

JunglePopulator::init();
