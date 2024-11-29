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

class SmoothMapLayer extends MapLayer{

	private MapLayer $below_layer;

	public function __construct(int $seed, MapLayer $below_layer){
		parent::__construct($seed);
		$this->below_layer = $below_layer;
	}

	public function generateValues(int $x, int $z, int $size_x, int $size_z) : array{
		$grid_x = $x - 1;
		$grid_z = $z - 1;
		$grid_size_x = $size_x + 2;
		$grid_size_z = $size_z + 2;
		$values = $this->below_layer->generateValues($grid_x, $grid_z, $grid_size_x, $grid_size_z);

		$final_values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				// This applies smoothing using Von Neumann neighborhood
				// it takes a 3x3 grid with a cross shape and analyzes values as follow
				// 0X0
				// XxX
				// 0X0
				// it is required that we use the same shape that was used for what we
				// want to smooth
				$upper_val = $values[$j + 1 + $i * $grid_size_x];
				$lower_val = $values[$j + 1 + ($i + 2) * $grid_size_x];
				$left_val = $values[$j + ($i + 1) * $grid_size_x];
				$right_val = $values[$j + 2 + ($i + 1) * $grid_size_x];
				$center_val = $values[$j + 1 + ($i + 1) * $grid_size_x];
				if($upper_val === $lower_val && $left_val === $right_val){
					$this->setCoordsSeed($x + $j, $z + $i);
					$center_val = $this->nextInt(2) === 0 ? $upper_val : $left_val;
				}elseif($upper_val === $lower_val){
					$center_val = $upper_val;
				}elseif($left_val === $right_val){
					$center_val = $left_val;
				}

				$final_values[$j + $i * $size_x] = $center_val;
			}
		}

		return $final_values;
	}
}
