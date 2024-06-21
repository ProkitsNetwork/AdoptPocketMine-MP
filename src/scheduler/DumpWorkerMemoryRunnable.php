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

namespace pocketmine\scheduler;

use GlobalLogger;
use pmmp\thread\Runnable;
use pmmp\thread\Thread as NativeThread;
use pocketmine\MemoryManager;
use PrefixedLogger;
use ReflectionClass;
use Symfony\Component\Filesystem\Path;

/**
 * Task used to dump memory from AsyncWorkers
 */
class DumpWorkerMemoryRunnable extends Runnable{
	public function __construct(
		private int $id,
		private string $outputFolder,
		private int $maxNesting,
		private int $maxStringSize
	){}

	public function run() : void{
		$worker = NativeThread::getCurrentThread();
		if($worker instanceof AsyncWorker){
			$path = Path::join($this->outputFolder, "AsyncWorker#$this->id#" . $worker->getAsyncWorkerId());
			$logger = $worker->getLogger();
		}else{
			$idName = (new ReflectionClass($worker::class))->getShortName() . "#" . $this->id;
			$path = Path::join($this->outputFolder, $idName);
			$logger = GlobalLogger::get();
			if(!($logger instanceof PrefixedLogger)){
				$logger = new PrefixedLogger($logger, $idName);
			}
		}
		MemoryManager::dumpMemory(
			$worker,
			$path,
			$this->maxNesting,
			$this->maxStringSize,
			new PrefixedLogger($logger, "Memory Dump")
		);
	}
}
