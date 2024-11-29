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

namespace pocketmine\world\generator\overworld\decorator;

use Exception;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Decorator;
use pocketmine\world\generator\object\tree\GenericTree;
use pocketmine\world\generator\overworld\decorator\types\TreeDecoration;

class TreeDecorator extends Decorator{

	/**
	 * @param TreeDecoration[] $decorations
	 * @return class-string<GenericTree>|null
	 */
	private static function getRandomTree(Random $random, array $decorations) : ?string{
		$total_weight = 0;
		foreach($decorations as $decoration){
			$total_weight += $decoration->weight;
		}

		if($total_weight > 0){
			$weight = $random->nextBoundedInt($total_weight);
			foreach($decorations as $decoration){
				$weight -= $decoration->weight;
				if($weight < 0){
					return $decoration->class;
				}
			}
		}

		return null;
	}

	/** @var TreeDecoration[] */
	private array $trees = [];

	final public function setTrees(TreeDecoration ...$trees) : void{
		$this->trees = $trees;
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$treeAmount = $this->amount;
		if($random->nextBoundedInt(10) === 0){
			++$treeAmount;
		}

		for($i = 0; $i < $treeAmount; ++$i){
			$this->decorate($world, $random, $chunk_x, $chunk_z, $chunk);
		}
	}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$x = $random->nextBoundedInt(16);
		$z = $random->nextBoundedInt(16);
		$source_y = $chunk->getHighestBlockAt($x, $z);

		$class = self::getRandomTree($random, $this->trees);
		if($class !== null){
			$txn = new BlockTransaction($world);
			try{
				/** @var GenericTree $tree */
				$tree = new $class($random, $txn);
			}catch(Exception $ex){
				$tree = new GenericTree($random, $txn);
			}
			if($tree->generate($world, $random, ($chunk_x << Chunk::COORD_BIT_SIZE) + $x, $source_y, ($chunk_z << Chunk::COORD_BIT_SIZE) + $z)){
				$txn->apply();
			}
		}
	}
}
