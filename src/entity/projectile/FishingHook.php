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

namespace pocketmine\entity\projectile;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\FishingRod;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use function abs;
use function sqrt;

class FishingHook extends Projectile{

	protected function getInitialDragMultiplier() : float{ return 0.04; }

	protected function getInitialGravity() : float{ return 0.04; }

	public static function getNetworkTypeId() : string{ return EntityIds::FISHING_HOOK; }

	public function canSaveWithChunk() : bool{ return false; }

	public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null, ?Vector3 $motion = null){
		parent::__construct($location, $shootingEntity, $nbt);
		$this->motion = $motion ?? new Vector3(0, 0, 0);
		if($shootingEntity instanceof Player){
			$shootingEntity->setFishingHook($this);
			$this->handleHookCasting($this->motion->x, $this->motion->y, $this->motion->z, 1.5, 1.0);
		}
	}

	public function handleHookCasting(float $x, float $y, float $z, float $ff1, float $ff2) : void{
		$f = sqrt($x * $x + $y * $y + $z * $z);
		$x /= $f;
		$y /= $f;
		$z /= $f;
		$x = $x + $this->random->nextSignedFloat() * 0.007499999832361937 * $ff2;
		$y = $y + $this->random->nextSignedFloat() * 0.007499999832361937 * $ff2;
		$z = $z + $this->random->nextSignedFloat() * 0.007499999832361937 * $ff2;
		$x *= $ff1;
		$y *= $ff1;
		$z *= $ff1;
		$this->motion->x = $x;
		$this->motion->y = $y;
		$this->motion->z = $z;
	}

	public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$entityHit->attack(new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 0));
		if($entityHit === $this->getOwningEntity()){
			$this->flagForDespawn();
			return;
		}
		$this->setTargetEntity($entityHit);
	}

	public function onUpdate(int $currentTick) : bool{
		$world = $this->getWorld();
		$position = $this->getPosition();
		$inBlock = $world->getBlock($position);
		$handled = false;
		if($inBlock->hasSameTypeId(VanillaBlocks::LAVA())){
			$this->flagForDespawn();
			$handled = true;
		}
		if($inBlock->hasSameTypeId(VanillaBlocks::WATER())){
			$liquidDepth = 0;
			$lastPosition = $position;
			while(true){
				if($world->getBlock($lastPosition)->hasSameTypeId(VanillaBlocks::WATER())){
					$liquidDepth++;
					$lastPosition->y++;
				}else{
					break;
				}
			}
			$motion = $this->getMotion();
			$motion->x *= 0.3;
			$motion->y *= 0.2;
			$motion->z *= 0.3;
			$d = $motion->y - $liquidDepth;
			if(abs($d) < 0.01){
				$d += $d >= 0 ? 0.1 : -0.1;
			}

			$motion->x *= 0.9;
			$motion->y -= $d * $this->random->nextFloat() * 0.2;
			$motion->z *= 0.9;
			$this->setMotion($motion->multiply(0.92));
			$handled = true;
		}
		if(!$handled && !$this->isFlaggedForDespawn()){
			$target = $this->getTargetEntity();
			if($target !== null){
				$pos = $target->getPosition();
				$pos->y += 0.8 * $target->getBoundingBox()->getYLength();
				$this->setPosition($pos);
			}
		}
		return parent::onUpdate($currentTick);
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		$this->validateLink();
		return parent::entityBaseTick($tickDiff);
	}

	private function validateLink() : void{
		$owner = $this->getOwningEntity();
		$target = $this->getTargetEntity();
		if($owner instanceof Player){
			if(
				$owner->isFlaggedForDespawn() ||
				$owner->isClosed() ||
				$owner->getFishing() !== $this ||
				!$owner->isAlive() ||
				!($owner->getInventory()->getItemInHand() instanceof FishingRod) ||
				$owner->getPosition()->distanceSquared($this->getPosition()) > 1024
			){
				$this->flagForDespawn();
			}
		}else{
			$this->flagForDespawn();
		}
		if($target !== null){
			if(!$target->isAlive() || $target->isFlaggedForDespawn() || $target->isClosed()){
				$this->setTargetEntity(null);
			}
		}
	}

	public function flagForDespawn() : void{
		parent::flagForDespawn();
		$owner = $this->getOwningEntity();
		if($owner instanceof Player){
			$owner->setFishingHook(null);
		}
	}

	public function retrieve() : void{
		$owner = $this->getOwningEntity();
		if($owner instanceof Player && $this->isValid()){
			$target = $this->getTargetEntity();
			if($target !== null){
				$owner = $owner->getPosition();
				$delta = $owner->subtractVector($target->getPosition())->multiply(0.1);
				$target->setMotion($target->getMotion()->addVector($delta));
			}
		}
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.25, 0.25);
	}
}
