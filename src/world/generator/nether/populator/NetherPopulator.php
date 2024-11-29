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

namespace pocketmine\world\generator\nether\populator;

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\nether\decorator\FireDecorator;
use pocketmine\world\generator\nether\decorator\GlowstoneDecorator;
use pocketmine\world\generator\nether\decorator\MushroomDecorator;
use pocketmine\world\generator\Populator;
use pocketmine\world\World;
use function array_push;

class NetherPopulator implements Populator{

	/** @var Populator[] */
	private array $in_ground_populators = [];

	/** @var Populator[] */
	private array $on_ground_populators = [];

	private OrePopulator $ore_populator;
	private FireDecorator $fire_decorator;
	private GlowstoneDecorator $glowstone_decorator_1;
	private GlowstoneDecorator $glowstone_decorator_2;
	private MushroomDecorator $brown_mushroom_decorator;
	private MushroomDecorator $red_mushroom_decorator;

	public function __construct(int $world_height = World::Y_MAX){
		$this->ore_populator = new OrePopulator($world_height);
		$this->in_ground_populators[] = $this->ore_populator;

		$this->fire_decorator = new FireDecorator();
		$this->glowstone_decorator_1 = new GlowstoneDecorator(true);
		$this->glowstone_decorator_2 = new GlowstoneDecorator();
		$this->brown_mushroom_decorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
		$this->red_mushroom_decorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());

		array_push($this->on_ground_populators,
			$this->fire_decorator,
			$this->glowstone_decorator_1,
			$this->glowstone_decorator_2,
			$this->fire_decorator,
			$this->brown_mushroom_decorator,
			$this->red_mushroom_decorator
		);

		$this->fire_decorator->setAmount(1);
		$this->glowstone_decorator_1->setAmount(1);
		$this->glowstone_decorator_2->setAmount(1);
		$this->brown_mushroom_decorator->setAmount(1);
		$this->red_mushroom_decorator->setAmount(1);
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$this->populateInGround($world, $random, $chunk_x, $chunk_z, $chunk);
		$this->populateOnGround($world, $random, $chunk_x, $chunk_z, $chunk);
	}

	private function populateInGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		foreach($this->in_ground_populators as $populator){
			$populator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		}
	}

	private function populateOnGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		foreach($this->on_ground_populators as $populator){
			$populator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		}
	}
}
