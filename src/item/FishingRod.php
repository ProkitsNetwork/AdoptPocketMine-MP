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

namespace pocketmine\item;

use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\FishingHook;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function cos;
use function sin;
use const M_PI;

class FishingRod extends Durable{

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getMaxDurability() : int{
		return 384;
	}

	public function getThrowForce() : float{
		return 0.4;
	}

	protected function createEntity(Location $location, Player $thrower, Vector3 $motion) : FishingHook{
		return new FishingHook($location, $thrower, null, $motion);
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		$fishingHook = $player->getFishing();
		if($fishingHook === null){
			$location = $player->getLocation();
			$radY = ($directionVector->y / 180) * M_PI;
			$x = cos($radY) * 0.16;
			$z = sin($radY) * 0.16;
			$projectile = $this->createEntity(Location::fromObject($player->getEyePos()->add(-$x, -0.1000000015, -$z), $player->getWorld(), $location->yaw, $location->pitch), $player, $directionVector->multiply($this->getThrowForce()));
			$projectile->spawnToAll();
		}else{
			if(!$fishingHook->isClosed()){
				$fishingHook->retrieve();
				$fishingHook->flagForDespawn();
			}else{
				$player->setFishingHook(null);
			}
			$this->applyDamage(1);
		}
		$player->broadcastAnimation(new ArmSwingAnimation($player));
		return ItemUseResult::SUCCESS();
	}
}
