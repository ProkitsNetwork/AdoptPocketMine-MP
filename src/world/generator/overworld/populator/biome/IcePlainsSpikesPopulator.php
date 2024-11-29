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

namespace pocketmine\world\generator\overworld\populator\biome;

use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\overworld\biome\BiomeIds;
use pocketmine\world\generator\overworld\decorator\IceDecorator;

class IcePlainsSpikesPopulator extends IcePlainsPopulator{

	protected IceDecorator $ice_decorator;

	public function __construct(){
		parent::__construct();
		$this->tall_grass_decorator->setAmount(0);
		$this->ice_decorator = new IceDecorator();
	}

	protected function populateOnGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$this->ice_decorator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		parent::populateOnGround($world, $random, $chunk_x, $chunk_z, $chunk);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::ICE_PLAINS_SPIKES];
	}
}
