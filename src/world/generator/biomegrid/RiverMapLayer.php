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

namespace pocketmine\world\generator\biomegrid;

use pocketmine\world\generator\overworld\biome\BiomeIds;
use function array_key_exists;

class RiverMapLayer extends MapLayer{

	/** @var int[] */
	private static array $OCEANS = [BiomeIds::OCEAN => 0, BiomeIds::DEEP_OCEAN => 0];

	/** @var int[] */
	private static array $SPECIAL_RIVERS = [
		BiomeIds::ICE_PLAINS => BiomeIds::FROZEN_RIVER,
		BiomeIds::MUSHROOM_ISLAND => BiomeIds::MUSHROOM_ISLAND_SHORE,
		BiomeIds::MUSHROOM_ISLAND_SHORE => BiomeIds::MUSHROOM_ISLAND_SHORE
	];

	private static int $CLEAR_VALUE = 0;
	private static int $RIVER_VALUE = 1;

	private MapLayer $below_layer;
	private ?MapLayer $merge_layer;

	public function __construct(int $seed, MapLayer $below_layer, ?MapLayer $merge_layer = null){
		parent::__construct($seed);
		$this->below_layer = $below_layer;
		$this->merge_layer = $merge_layer;
	}

	public function generateValues(int $x, int $z, int $size_x, int $size_z) : array{
		if($this->merge_layer === null){
			return $this->generateRivers($x, $z, $size_x, $size_z);
		}

		return $this->mergeRivers($x, $z, $size_x, $size_z);
	}

	/**
	 * @return int[]
	 */
	private function generateRivers(int $x, int $z, int $size_x, int $size_z) : array{
		$grid_x = $x - 1;
		$grid_z = $z - 1;
		$grid_size_x = $size_x + 2;
		$grid_size_z = $size_z + 2;

		$values = $this->below_layer->generateValues($grid_x, $grid_z, $grid_size_x, $grid_size_z);
		$final_values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				// This applies rivers using Von Neumann neighborhood
				$center_val = $values[$j + 1 + ($i + 1) * $grid_size_x] & 1;
				$upper_val = $values[$j + 1 + $i * $grid_size_x] & 1;
				$lower_val = $values[$j + 1 + ($i + 2) * $grid_size_x] & 1;
				$left_val = $values[$j + ($i + 1) * $grid_size_x] & 1;
				$right_val = $values[$j + 2 + ($i + 1) * $grid_size_x] & 1;
				$val = self::$CLEAR_VALUE;
				if($center_val !== $upper_val || $center_val !== $lower_val || $center_val !== $left_val || $center_val !== $right_val){
					$val = self::$RIVER_VALUE;
				}
				$final_values[$j + $i * $size_x] = $val;
			}
		}
		return $final_values;
	}

	/**
	 * @return int[]
	 */
	private function mergeRivers(int $x, int $z, int $size_x, int $size_z) : array{
		$values = $this->below_layer->generateValues($x, $z, $size_x, $size_z);
		$merge_values = $this->merge_layer->generateValues($x, $z, $size_x, $size_z);

		$final_values = [];
		for($i = 0; $i < $size_x * $size_z; ++$i){
			$val = $merge_values[$i];
			if(array_key_exists($merge_values[$i], self::$OCEANS)){
				$val = $merge_values[$i];
			}elseif($values[$i] === self::$RIVER_VALUE){
				$val = self::$SPECIAL_RIVERS[$merge_values[$i]] ?? BiomeIds::RIVER;
			}
			$final_values[$i] = $val;
		}

		return $final_values;
	}
}
