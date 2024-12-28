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

use Closure;
use pmmp\thread\ThreadSafe;
use function igbinary_serialize;
use function igbinary_unserialize;
use function is_string;
use function spl_object_id;

/**
 * @template TContext
 * @template TReturn
 */
class FutureResolver extends ThreadSafe{
	/** @var FutureSharedData<TReturn> */
	private FutureSharedData $data;
	/** @phpstan-var ThreadSafe|anyClosure|string|null  */
	private ThreadSafe|Closure|string|null $context;
	/**
	 * @internal
	 * @var FutureResolver[]
	 * @phpstan-var array<int, FutureResolver>
	 */
	public static array $neverDestruct = [];

	/**
	 * @param TContext $context
	 */
	public function __construct($context){
		$this->setContext($context);
		$this->data = new FutureSharedData();
		$id = spl_object_id($this);
		$this->data->resolver = $id;
		self::$neverDestruct[$id] = $this;
	}

	/**
	 * @param TContext $context
	 */
	private function setContext($context) : void{
		if(!$context instanceof Closure && !$context instanceof ThreadSafe && $context !== null){
			$this->context = igbinary_serialize($context);
			return;
		}
		$this->context = $context;
	}

	/**
	 * @return TContext
	 */
	public function getContext(){
		if(is_string($this->context)){
			return igbinary_unserialize($this->context);
		}
		return $this->context;
	}

	/**
	 * @param TReturn $value
	 */
	public function finish($value) : void{
		$this->data->setValue($value);
		$this->data->done = true;
	}

	public function crash(string $info) : void{
		$this->data->done = true;
		$this->data->crashed = true;
		$this->data->crashMessage = $info;
	}

	/**
	 * @return Future<TReturn>
	 */
	public function future() : Future{
		return new Future($this->data);
	}

	/**
	 * @param Closure():void $c
	 */
	public function do(Closure $c) : void{
		try{
			$this->finish($c());
		}catch(\Throwable $throwable){
			\GlobalLogger::get()->logException($throwable);
			$this->crash($throwable->getMessage());
		}
	}

	public function isCancelled() : bool{
		return $this->data->cancelled;
	}
}
