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

namespace pocketmine\world\generator;

use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\biomegrid\MapLayer;
use pocketmine\world\generator\biomegrid\utils\MapLayerPair;
use pocketmine\world\generator\overworld\WorldType;
use pocketmine\world\generator\utils\preset\GeneratorPreset;
use pocketmine\world\generator\utils\WorldOctaves;
use pocketmine\world\World;
use function array_push;
use function count;

/**
 * @template T of WorldOctaves
 */
abstract class VanillaGenerator extends Generator{

	private ?WorldOctaves $octave_cache = null;

	/** @var Populator[] */
	private array $populators = [];

	private MapLayerPair $biome_grid;

	public function __construct(int $seed, int $environment, ?string $world_type, GeneratorPreset $preset){
		parent::__construct($seed, $preset->toString());
		$this->biome_grid = MapLayer::initialize($seed, $environment, $world_type ?? WorldType::NORMAL);
	}

	/**
	 * @return int[]
	 */
	public function getBiomeGridAtLowerRes(int $x, int $z, int $size_x, int $size_z) : array{
		return $this->biome_grid->low_resolution->generateValues($x, $z, $size_x, $size_z);
	}

	/**
	 * @return int[]
	 */
	public function getBiomeGrid(int $x, int $z, int $size_x, int $size_z) : array{
		return $this->biome_grid->high_resolution->generateValues($x, $z, $size_x, $size_z);
	}

	protected function addPopulators(Populator ...$populators) : void{
		array_push($this->populators, ...$populators);
	}

	abstract protected function createWorldOctaves() : WorldOctaves;

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$biomes = new VanillaBiomeGrid();
		$biome_values = $this->biome_grid->high_resolution->generateValues($chunkX * 16, $chunkZ * 16, 16, 16);
		for($i = 0, $biome_values_c = count($biome_values); $i < $biome_values_c; ++$i){
			$biomes->biomes[$i] = $biome_values[$i];
		}

		$this->generateChunkData($world, $chunkX, $chunkZ, $biomes);
	}

	abstract protected function generateChunkData(ChunkManager $world, int $chunk_x, int $chunk_z, VanillaBiomeGrid $biomes) : void;

	final protected function getWorldOctaves() : WorldOctaves{
		return $this->octave_cache ??= $this->createWorldOctaves();
	}

	/**
	 * @return Populator[]
	 */
	public function getDefaultPopulators() : array{
		return $this->populators;
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		/** @var Chunk $chunk */
		$chunk = $world->getChunk($chunkX, $chunkZ);
		foreach($this->populators as $populator){
			$populator->populate($world, $this->random, $chunkX, $chunkZ, $chunk);
		}
	}

	public function getMaxY() : int{
		return World::Y_MAX;
	}
}
