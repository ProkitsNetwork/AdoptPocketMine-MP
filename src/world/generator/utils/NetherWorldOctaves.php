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

namespace pocketmine\world\generator\utils;

use pocketmine\world\generator\noise\bukkit\OctaveGenerator;

/**
 * @template T of OctaveGenerator
 * @template U of OctaveGenerator
 * @template V of OctaveGenerator
 * @template W of OctaveGenerator
 * @template X of OctaveGenerator
 * @template Y of OctaveGenerator
 *
 * @extends WorldOctaves<T, U, V, W>
 */
class NetherWorldOctaves extends WorldOctaves{

	public function __construct(
		OctaveGenerator $height,
		OctaveGenerator $roughness,
		OctaveGenerator $roughness_2,
		OctaveGenerator $detail,
		OctaveGenerator $surface,
		public OctaveGenerator $soul_sand,
		public OctaveGenerator $gravel
	){
		parent::__construct($height, $roughness, $roughness_2, $detail, $surface);
	}
}
