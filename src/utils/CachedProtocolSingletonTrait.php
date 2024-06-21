<?php

namespace pocketmine\utils;

use pocketmine\data\FilesystemCache;
use ReflectionClass;

trait CachedProtocolSingletonTrait{
	use ProtocolSingletonTrait;

	private static function make(int $protocolId) : self{
		$key = "protocol_singleton_{$protocolId}_" . (new ReflectionClass(__CLASS__))->getShortName();
		$cache = FilesystemCache::getInstance();
		$v = $cache->get($key);
		if($v instanceof self){
			return $v;
		}
		$v = self::makeCached($protocolId);
		$cache->put($key, $v);
		return $v;
	}

	private static function makeCached(int $protocolId) : self{
		return new self($protocolId);
	}
}