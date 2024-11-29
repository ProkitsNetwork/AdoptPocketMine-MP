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

namespace pocketmine\world\generator\object\tree;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function abs;

class TallRedwoodTree extends RedwoodTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(
			BlockTypeIds::AIR,
			BlockTypeIds::ACACIA_LEAVES,
			BlockTypeIds::BIRCH_LEAVES,
			BlockTypeIds::DARK_OAK_LEAVES,
			BlockTypeIds::JUNGLE_LEAVES,
			BlockTypeIds::OAK_LEAVES,
			BlockTypeIds::SPRUCE_LEAVES,
			BlockTypeIds::GRASS,
			BlockTypeIds::DIRT,
			BlockTypeIds::ACACIA_WOOD,
			BlockTypeIds::BIRCH_WOOD,
			BlockTypeIds::DARK_OAK_WOOD,
			BlockTypeIds::JUNGLE_WOOD,
			BlockTypeIds::OAK_WOOD,
			BlockTypeIds::SPRUCE_WOOD,
			BlockTypeIds::ACACIA_SAPLING,
			BlockTypeIds::BIRCH_SAPLING,
			BlockTypeIds::DARK_OAK_SAPLING,
			BlockTypeIds::JUNGLE_SAPLING,
			BlockTypeIds::OAK_SAPLING,
			BlockTypeIds::SPRUCE_SAPLING,
			BlockTypeIds::VINES
		);
		$this->setHeight($random->nextBoundedInt(5) + 7);
		$this->setLeavesHeight($this->height - $random->nextBoundedInt(2) - 3);
		$this->setMaxRadius($random->nextBoundedInt($this->height - $this->leaves_height + 1) + 1);
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($this->cannotGenerateAt($source_x, $source_y, $source_z, $world)){
			return false;
		}

		// generate the leaves
		$radius = 0;
		for($y = $source_y + $this->height; $y >= $source_y + $this->leaves_height; --$y){
			// leaves are built from top to bottom
			for($x = $source_x - $radius; $x <= $source_x + $radius; ++$x){
				for($z = $source_z - $radius; $z <= $source_z + $radius; ++$z){
					if(
						(
							abs($x - $source_x) !== $radius ||
							abs($z - $source_z) !== $radius ||
							$radius <= 0
						) &&
						$world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR
					){
						$this->transaction->addBlockAt($x, $y, $z, $this->leaves_type);
					}
				}
			}
			if($radius >= 1 && $y === $source_y + $this->leaves_height + 1){
				--$radius;
			}elseif($radius < $this->max_radius){
				++$radius;
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->height - 1; ++$y){
			$this->replaceIfAirOrLeaves($source_x, $source_y + $y, $source_z, $this->log_type, $world);
		}

		$this->transaction->addBlockAt($source_x, $source_y - 1, $source_z, VanillaBlocks::DIRT());
		return true;
	}
}
