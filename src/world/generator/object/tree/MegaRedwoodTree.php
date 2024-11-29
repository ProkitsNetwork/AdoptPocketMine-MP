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

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function floor;

class MegaRedwoodTree extends MegaJungleTree{

	protected int $leaves_height;

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(15) + $random->nextBoundedInt(3) + 13);
		$this->setType(VanillaBlocks::SPRUCE_LOG(), VanillaBlocks::SPRUCE_LEAVES());
		$this->setLeavesHeight($random->nextBoundedInt(5) + ($random->nextBoolean() ? 3 : 13));
	}

	protected function setLeavesHeight(int $leaves_height) : void{
		$this->leaves_height = $leaves_height;
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($this->cannotGenerateAt($source_x, $source_y, $source_z, $world)){
			return false;
		}

		// generates the leaves
		$previous_radius = 0;
		for($y = $source_y + $this->height - $this->leaves_height; $y <= $source_y + $this->height; ++$y){
			$n = $source_y + $this->height - $y;
			$radius = (int) floor((float) $n / $this->leaves_height * 3.5);
			if($radius === $previous_radius && $n > 0 && $y % 2 === 0){
				++$radius;
			}
			$this->generateLeaves($source_x, $y, $source_z, $radius, false, $world);
			$previous_radius = $radius;
		}

		// generates the trunk
		$this->generateTrunk($world, $source_x, $source_y, $source_z);

		// blocks below trunk are always dirt
		$this->generateDirtBelowTrunk($source_x, $source_y, $source_z);
		return true;
	}

	protected function generateDirtBelowTrunk(int $block_x, int $block_y, int $block_z) : void{
		// mega redwood tree does not replaces blocks below (surely to preserves podzol)
	}
}
