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

namespace pocketmine\network\mcpe\cache;

use pocketmine\data\FilesystemCache;
use pocketmine\data\FilesystemCacheKey;
use pocketmine\inventory\CreativeInventory;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use pocketmine\timings\Timings;
use pocketmine\utils\ProtocolSingletonTrait;
use function is_string;
use function spl_object_id;

final class CreativeInventoryCache{
	use ProtocolSingletonTrait;

	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $caches = [];

	public function getCache(CreativeInventory $inventory) : string{
		$isDefault = $inventory === CreativeInventory::getInstance();
		$id = spl_object_id($inventory);
		if(!isset($this->caches[$id])){
			$inventory->getDestructorCallbacks()->add(function() use ($id) : void{
				unset($this->caches[$id]);
			});
			$inventory->getContentChangedCallbacks()->add(function() use ($id) : void{
				unset($this->caches[$id]);
			});
			$this->caches[$id] = $this->getCraftingDataPayload($inventory,$isDefault);
		}
		return $this->caches[$id];
	}

	private function getCraftingDataPayload(CreativeInventory $inventory, bool $isDefault) : string{
		if(!$isDefault){
			return $this->buildCreativeInventoryCache($inventory);
		}
		return FilesystemCache::getInstance()->getOrDefault(
			FilesystemCacheKey::getCreativeInventory($this->protocolId),
			fn() => $this->buildCreativeInventoryCache($inventory),
			static fn($val) => is_string($val)
		);
	}

	/**
	 * Rebuild the cache for the given inventory.
	 */
	private function buildCreativeInventoryCache(CreativeInventory $inventory) : string{
		Timings::$creativeContentCacheRebuild->startTiming();
		Timings::$creativeContentCacheShade->startTiming();
		$entries = [];
		$typeConverter = TypeConverter::getInstance($this->protocolId);
		//creative inventory may have holes if items were unregistered - ensure network IDs used are always consistent
		foreach($inventory->getAll() as $k => $item){
			$entries[] = new CreativeContentEntry($k, $typeConverter->coreItemStackToNet($item));
		}

		$packet = CreativeContentPacket::create($entries);
		$out = PacketSerializer::encoder($this->protocolId);
		Timings::$creativeContentCacheShade->stopTiming();
		NetworkSession::encodePacketTimed($out, $packet);
		Timings::$creativeContentCacheRebuild->stopTiming();
		return $out->getBuffer();
	}
}
