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
use pocketmine\world\generator\object\tree\AcaciaTree;
use pocketmine\world\generator\object\tree\GenericTree;
use pocketmine\world\generator\overworld\biome\BiomeIds;
use pocketmine\world\generator\overworld\decorator\types\DoublePlantDecoration;
use pocketmine\world\generator\overworld\decorator\types\TreeDecoration;

class SavannaPopulator extends BiomePopulator{

	/** @var DoublePlantDecoration[] */
	protected static array $DOUBLE_PLANTS;

	/** @var TreeDecoration[] */
	protected static array $TREES;

	public static function init() : void{
		parent::init();
		self::$DOUBLE_PLANTS = [
			new DoublePlantDecoration(VanillaBlocks::DOUBLE_TALLGRASS(), 1)
		];
	}

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(AcaciaTree::class, 4),
			new TreeDecoration(GenericTree::class, 4)
		];
	}

	protected function initPopulators() : void{
		$this->double_plant_decorator->setAmount(7);
		$this->double_plant_decorator->setDoublePlants(...self::$DOUBLE_PLANTS);
		$this->tree_decorator->setAmount(1);
		$this->tree_decorator->setTrees(...self::$TREES);
		$this->flower_decorator->setAmount(4);
		$this->tall_grass_decorator->setAmount(20);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::SAVANNA, BiomeIds::SAVANNA_PLATEAU];
	}
}
SavannaPopulator::init();
