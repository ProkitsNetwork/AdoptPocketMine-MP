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
use pocketmine\thread\ThreadCrashInfo;

/**
 * @template C
 * @template T
 */
class FutureResolver extends ThreadSafe{
	/** @var FutureSharedData<T> */
	private FutureSharedData $data;
	private $context = null;
	private static array $neverDestruct = [];

	/**
	 * @param C $context
	 */
	public function __construct($context = null){
		$this->setContext($context);
		$this->data = new FutureSharedData();
		self::$neverDestruct[] = $this;
	}

	private function setContext($context) : void{
		if(!$context instanceof \Closure && !$context instanceof ThreadSafe){
			$this->context = igbinary_serialize($context);
			return;
		}
		$this->context = $context;
	}

	/**
	 * @return C
	 */
	public function getContext(){
		if(!$this->context instanceof \Closure && !$this->context instanceof ThreadSafe){
			return igbinary_unserialize($this->context);
		}
		return $this->context;
	}

	/**
	 * @param T $value
	 */
	public function finish($value) : void{
		var_dump(get_debug_type($value));
		$this->data->done = true;
		$this->data->setValue($value);
	}

	public function crash($info) : void{
		$this->data->done = true;
		$this->data->crashed = true;
		$this->data->crash = $info;
	}

	/**
	 * @return Future<T>
	 */
	public function future() : Future{
		return new Future($this->data);
	}

	public function do(\Closure $c) : void{
		try{
			$this->finish($c());
		}catch(\Throwable $throwable){
			\GlobalLogger::get()->logException($throwable);
			$f = new \ReflectionFunction($c);
			$class = $f->getClosureScopeClass()?->getShortName() ?? 'Unknown';
			$this->crash(ThreadCrashInfo::constructor($throwable, $class));
		}
	}
}
