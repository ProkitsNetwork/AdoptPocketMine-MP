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

namespace pocketmine\network\mcpe;

use Closure;
use pmmp\thread\ThreadSafe;
use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\SkinAdapter;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\scheduler\AsyncTask;

class ProcessSkinTask extends AsyncTask{
	private const KEY_ON_COMPLETION = 'onCompletion';

	private ?string $error = 'Unknown';

	/**
	 * @param Closure(?Skin $skin,?string $error) : void $callback
	 */
	public function __construct(
		private SkinAdapter&ThreadSafe $adapter,
		private SkinData $skin,
		Closure $callback,
	){
		$this->storeLocal(self::KEY_ON_COMPLETION, $callback);
	}

	public function onRun() : void{
		try{
			$skin = $this->adapter->fromSkinData($this->skin);
			$this->setResult($skin);
			$this->error = null;
		}catch(InvalidSkinException $e){
			$this->error = $e->getMessage();
		}
	}

	public function onCompletion() : void{
		$result = $this->getResult();
		if(!($result instanceof Skin)){
			$result = null;
		}
		/** @var Closure(?Skin $skin,?string $error) : void $callback */
		$callback = $this->fetchLocal(self::KEY_ON_COMPLETION);
		($callback)($result, $this->error);
	}
}
