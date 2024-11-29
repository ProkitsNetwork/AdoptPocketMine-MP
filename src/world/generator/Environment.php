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

namespace pocketmine\world\generator;

use InvalidArgumentException;
use function strtolower;

final class Environment{

	public static function fromString(string $string) : int{
		return match(strtolower($string)){
			"overworld" => self::OVERWORLD,
			"nether" => self::NETHER,
			"end", "the_end" => self::THE_END,
			default => throw new InvalidArgumentException("Could not convert string \"{$string}\" to a " . self::class . " constant")
		};
	}

	public const OVERWORLD = 0;
	public const NETHER = -1;
	public const THE_END = 1;

	private function __construct(){
	}
}
