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

namespace pocketmine\world\generator\biomegrid\utils;

final class BiomeEdgeEntry{

	/** @var array<int, int> */
	public array $key;

	/** @var int[]|null */
	public ?array $value = null;

	/**
	 * @param array<int, int> $mapping
	 * @param int[]           $value
	 */
	public function __construct(array $mapping, ?array $value = null){
		$this->key = $mapping;
		if($value !== null){
			$this->value = [];
			foreach($value as $v){
				$this->value[$v] = $v;
			}
		}
	}
}
