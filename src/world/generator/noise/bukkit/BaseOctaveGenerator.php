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

namespace pocketmine\world\generator\noise\bukkit;

abstract class BaseOctaveGenerator{

	public float $x_scale = 1.0;
	public float $y_scale = 1.0;
	public float $z_scale = 1.0;

	/**
	 * @param NoiseGenerator[] $octaves
	 */
	protected function __construct(
		protected array $octaves
	){}

	/**
	 * Sets the scale used for all coordinates passed to this generator.
	 * <p>
	 * This is the equivalent to setting each coordinate to the specified
	 * value.
	 *
	 * @param float $scale New value to scale each coordinate by
	 */
	public function setScale(float $scale) : void{
		$this->x_scale = $scale;
		$this->y_scale = $scale;
		$this->z_scale = $scale;
	}

	/**
	 * Gets a clone of the individual octaves used within this generator
	 *
	 * @return NoiseGenerator[] clone of the individual octaves
	 */
	public function getOctaves() : array{
		$octaves = [];
		foreach($this->octaves as $key => $value){
			$octaves[$key] = clone $value;
		}

		return $octaves;
	}
}
