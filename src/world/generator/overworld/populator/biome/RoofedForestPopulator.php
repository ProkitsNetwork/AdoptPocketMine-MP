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

use pocketmine\world\generator\object\tree\BirchTree;
use pocketmine\world\generator\object\tree\BrownMushroomTree;
use pocketmine\world\generator\object\tree\DarkOakTree;
use pocketmine\world\generator\object\tree\GenericTree;
use pocketmine\world\generator\object\tree\RedMushroomTree;
use pocketmine\world\generator\overworld\biome\BiomeIds;
use pocketmine\world\generator\overworld\decorator\types\TreeDecoration;

class RoofedForestPopulator extends ForestPopulator{

	private const BIOMES = [BiomeIds::ROOFED_FOREST, BiomeIds::ROOFED_FOREST_MUTATED];

	/** @var TreeDecoration[] */
	protected static array $TREES;

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(GenericTree::class, 20),
			new TreeDecoration(BirchTree::class, 5),
			new TreeDecoration(RedMushroomTree::class, 2),
			new TreeDecoration(BrownMushroomTree::class, 2),
			new TreeDecoration(DarkOakTree::class, 50)
		];
	}

	protected function initPopulators() : void{
		$this->tree_decorator->setAmount(50);
		$this->tree_decorator->setTrees(...self::$TREES);
		$this->tall_grass_decorator->setAmount(4);
	}

	public function getBiomes() : ?array{
		return self::BIOMES;
	}
}

RoofedForestPopulator::init();
