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
use pmmp\thread\ThreadSafeArray;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\format\io\LoadedChunkData;
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
	private $value;
	private $isChunkData = false;
	private $typ;

	public function __construct(){
	}

	public function setValue($value) : void{
		$this->typ=(get_debug_type($value));
		if($value === null){
			\GlobalLogger::get()->error("FJjjjjjEIJFIEJ");
		}
		if($value instanceof LoadedChunkData){
			$this->value = FastChunkSerializer::serializeLoadedChunkData($value);
			$this->isChunkData = true;
			return;
		}
		if($value instanceof ThreadSafeArray){
			$this->value = $value;
			return;
		}
		$value = igbinary_serialize($value);
		$this->value = $value;
		if($this->value instanceof ThreadSafeArray){
			throw new \RuntimeException();
		}
	}

	public function getValue(){
		var_dump("START");
		var_dump($this->isChunkData);
		var_dump(get_debug_type($this->value));
		var_dump(($this->typ));
		var_dump("END");
		if($this->value === null){
			return null;
		}
		if($this->isChunkData){
			return FastChunkSerializer::deserializeLoadedChunkData($this->value);
		}
		if($this->value instanceof ThreadSafeArray){
			return $this->value;
		}
		$value = igbinary_unserialize($this->value);
		return $value;
	}
}
