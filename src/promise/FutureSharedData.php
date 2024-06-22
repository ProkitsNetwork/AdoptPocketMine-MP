<?php
declare(strict_types=1);
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

namespace pocketmine\promise;

use pmmp\thread\ThreadSafe;
use function igbinary_serialize;
use function igbinary_unserialize;

/**
 * @internal
 * @see FutureResolver
 * @template T
 */
class FutureSharedData extends ThreadSafe{
	public bool $done = false;
	public bool $crashed = false;
	public $crash;
	private ?string $value;

	public function __construct(){
	}

	public function setValue($value) : void{
		$this->value = igbinary_serialize($value);
	}

	public function getValue(){
		if($this->value === null){
			return null;
		}
		return igbinary_unserialize($this->value);
	}
}
