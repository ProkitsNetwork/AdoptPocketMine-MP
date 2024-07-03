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

namespace pocketmine\utils;

use pocketmine\data\FilesystemCache;
use ReflectionClass;

trait CachedProtocolSingletonTrait{
	use ProtocolSingletonTrait;

	private static function make(int $protocolId) : self{
		$key = "protocol_singleton_{$protocolId}_" . (new ReflectionClass(__CLASS__))->getShortName();
		return FilesystemCache::getInstance()->getOrDefault(
			$key,
			fn() => self::makeCached($protocolId),
			fn($val) => $val instanceof self
		);
	}

	private static function makeCached(int $protocolId) : self{
		return new self($protocolId);
	}
}
