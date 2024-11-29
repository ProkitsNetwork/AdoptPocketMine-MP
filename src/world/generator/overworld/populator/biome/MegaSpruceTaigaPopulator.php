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

use pocketmine\world\generator\object\tree\MegaSpruceTree;
use pocketmine\world\generator\object\tree\RedwoodTree;
use pocketmine\world\generator\object\tree\TallRedwoodTree;
use pocketmine\world\generator\overworld\biome\BiomeIds;
use pocketmine\world\generator\overworld\decorator\types\TreeDecoration;

class MegaSpruceTaigaPopulator extends MegaTaigaPopulator{

	/** @var TreeDecoration[] */
	protected static array $TREES;

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(RedwoodTree::class, 44),
			new TreeDecoration(TallRedwoodTree::class, 22),
			new TreeDecoration(MegaSpruceTree::class, 33)
		];
	}

	public function getBiomes() : ?array{
		return [BiomeIds::REDWOOD_TAIGA_MUTATED, BiomeIds::REDWOOD_TAIGA_HILLS_MUTATED];
	}

	protected function initPopulators() : void{
		$this->tree_decorator->setTrees(...self::$TREES);
	}
}

MegaSpruceTaigaPopulator::init();
