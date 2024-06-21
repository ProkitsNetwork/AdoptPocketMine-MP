<?php

namespace pocketmine\data;

use Closure;
use pocketmine\utils\Utils;

class FilesystemMutex{
	private bool $locked = false;

	public function __construct(
		private string $path,
	){
	}

	private function acquire() : Closure{
		if($this->locked){
			return static fn() => null;
		}
		$fp = Utils::assumeNotFalse(fopen($this->path, 'wb+'));
		Utils::assumeNotFalse(flock($fp, LOCK_EX));
		$this->locked = true;
		return function() use ($fp){
			flock($fp, LOCK_UN);
			fclose($fp);
			$this->locked = false;
		};
	}

	public function do(Closure $c){
		$b = $this->acquire();
		try{
			return $c();
		}finally{
			$b();
		}
	}
}