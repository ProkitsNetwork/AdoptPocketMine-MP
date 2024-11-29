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
use pocketmine\world\generator\object\tree\TallBirchTree;
use pocketmine\world\generator\overworld\biome\BiomeIds;
use pocketmine\world\generator\overworld\decorator\types\TreeDecoration;

class BirchForestMountainsPopulator extends ForestPopulator{

	private const BIOMES = [BiomeIds::BIRCH_FOREST_MUTATED, BiomeIds::BIRCH_FOREST_HILLS_MUTATED];

	/** @var TreeDecoration[] */
	protected static array $TREES;

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(BirchTree::class, 1),
			new TreeDecoration(TallBirchTree::class, 1)
		];
	}

	protected function initPopulators() : void{
		$this->tree_decorator->setTrees(...self::$TREES);
	}

	public function getBiomes() : ?array{
		return self::BIOMES;
	}
}

BirchForestMountainsPopulator::init();
