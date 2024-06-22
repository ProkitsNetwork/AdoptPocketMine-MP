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

namespace pocketmine\thread;

use pmmp\thread\ThreadSafe;
use function serialize;
use function unserialize;

final class ThreadCrashInfoFrame extends ThreadSafe implements \Serializable{

	public function __construct(
		private string $printableFrame,
		private ?string $file,
		private int $line,
	){}

	public function getPrintableFrame() : string{ return $this->printableFrame; }

	public function getFile() : ?string{ return $this->file; }

	public function getLine() : int{ return $this->line; }

	public function serialize(){
		return serialize([$this->printableFrame, $this->file, $this->line]);
	}

	public function unserialize(string $data){
		[$this->printableFrame, $this->file, $this->line] = unserialize($data);
	}

	public function __serialize() : array{
		return [$this->printableFrame, $this->file, $this->line];
	}

	public function __unserialize(array $data) : void{
		[$this->printableFrame, $this->file, $this->line] = $data;
	}
}
