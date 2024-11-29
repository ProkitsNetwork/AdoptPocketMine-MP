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

namespace pocketmine\world\generator\object;

use pocketmine\block\Flowable;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;

abstract class TerrainObject{

	/**
	 * Removes weak blocks like grass, shrub, flower or mushroom directly above the given block, if present.
	 * Does not drop an item.
	 *
	 * @return bool whether a block was removed; false if none was present
	 */
	public static function killWeakBlocksAbove(ChunkManager $world, int $x, int $y, int $z) : bool{
		$cur_y = $y + 1;
		$changed = false;

		while($cur_y < World::Y_MAX){
			$block = $world->getBlockAt($x, $cur_y, $z);
			if(!($block instanceof Flowable)){
				break;
			}
			$world->setBlockAt($x, $cur_y, $z, VanillaBlocks::AIR());
			$changed = true;
			++$cur_y;
		}

		return $changed;
	}

	/**
	 * Generates this feature.
	 *
	 * @param ChunkManager $world    the world to generate in
	 * @param Random       $random   the PRNG that will choose the size and a few details of the shape
	 * @param int          $source_x the base X coordinate
	 * @param int          $source_y the base Y coordinate
	 * @param int          $source_z the base Z coordinate
	 * @return bool if successfully generated
	 */
	abstract public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool;
}
