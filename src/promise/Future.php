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

namespace pocketmine\promise;

use pmmp\thread\ThreadSafe;
use pocketmine\thread\ThreadCrashException;
use function usleep;

/**
 * @template V
 */
class Future extends ThreadSafe{
	/**
	 * @internal
	 * @see FutureResolver
	 */
	public function __construct(
		private FutureSharedData $data
	){

	}

	/**
	 * @return V
	 * @throws \RuntimeException
	 */
	public function get(){
		while(!$this->data->done){
			usleep(50);
		}
		if(isset($this->data->crash)){
			throw new ThreadCrashException('Future crashed', $this->data->crash);
		}
		return $this->data->getValue();
	}

	/**
	 * @return \Generator|V
	 * @throws \RuntimeException
	 */
	public function getGenerator(){
		while(!$this->data->done){
			yield;
		}
		if(isset($this->data->crash)){
			throw new ThreadCrashException('Future crashed', $this->data->crash);
		}
		return $this->data->getValue();
	}

	public function isDone() : bool{
		return $this->data->done;
	}
}
