<?php

namespace pocketmine\data;

class FilesystemCacheKey{
	public const STRING_TO_ITEM_PARSER = 'string_to_item_parser';

	public static function getCraftingDataKey(int $protocol) : string{
		return "crafting_data_$protocol";
	}

	public static function getCreativeInventory(int $protocol) : string{
		return "creative_inventory_$protocol";
	}
}