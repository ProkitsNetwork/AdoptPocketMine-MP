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

namespace pocketmine\world\generator\overworld;

use InvalidArgumentException;
use function strtolower;

final class WorldType{

	public static function fromString(string $string) : string{
		return match(strtolower($string)){
			"amplified" => self::AMPLIFIED,
			"default_1_1", "version_1_1" => self::VERSION_1_1,
			"flat" => self::FLAT,
			"largebiomes", "large_biomes" => self::LARGE_BIOMES,
			"normal" => self::NORMAL,
			default => throw new InvalidArgumentException("Could not convert string \"{$string}\" to a " . self::class . " constant")
		};
	}

	public const AMPLIFIED = "AMPLIFIED";
	public const FLAT = "FLAT";
	public const LARGE_BIOMES = "LARGEBIOMES";
	public const NORMAL = "DEFAULT";
	public const VERSION_1_1 = "DEFAULT_1_1";

	private function __construct(){
	}
}
