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

namespace pocketmine;

use pocketmine\plugin\PluginBase;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\generator\nether\NetherGenerator;
use pocketmine\world\generator\overworld\OverworldGenerator;

final class Loader extends PluginBase{

	public function onLoad() : void{
		$generator_manager = GeneratorManager::getInstance();
		$generator_manager->addGenerator(NetherGenerator::class, "vanilla_nether", fn() => null);
		$generator_manager->addGenerator(OverworldGenerator::class, "vanilla_overworld", fn() => null);
	}
}
