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

namespace pocketmine\world\generator\ground;

use pocketmine\block\utils\DirtType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class DirtPatchGroundGenerator extends GroundGenerator{

	public function generateTerrainColumn(ChunkManager $world, Random $random, int $x, int $z, int $biome, float $surface_noise) : void{
		$this->setTopMaterial(match(true){
			$surface_noise > 1.75 => VanillaBlocks::DIRT()->setDirtType(DirtType::COARSE),
			$surface_noise > -0.95 => VanillaBlocks::PODZOL(),
			default => VanillaBlocks::GRASS()
		});
		$this->setGroundMaterial(VanillaBlocks::DIRT());
		parent::generateTerrainColumn($world, $random, $x, $z, $biome, $surface_noise);
	}
}
