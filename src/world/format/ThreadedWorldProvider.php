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

namespace pocketmine\world\format;

use pocketmine\promise\Future;
use pocketmine\world\format\io\ChunkData;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use pocketmine\world\format\io\LoadedChunkData;
use pocketmine\world\format\io\WorldData;

interface ThreadedWorldProvider{
	/**
	 * Returns the lowest buildable Y coordinate of this world
	 */
	public function getWorldMinY() : int;

	/**
	 * Gets the build height limit of this world
	 */
	public function getWorldMaxY() : int;

	/**
	 * Loads a chunk (usually from disk storage) and returns it. If the chunk does not exist, null is returned.
	 *
	 * @return Future<LoadedChunkData|null>
	 * @throws CorruptedChunkException
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : Future;

	/**
	 * Saves a chunk to disk storage.
	 * @return Future<void>
	 */
	public function saveChunk(int $chunkX, int $chunkZ, ChunkData $chunkData, int $dirtyFlags) : Future;
	/**
	 * Performs garbage collection in the world provider, such as cleaning up regions in Region-based worlds.
	 * @return Future<void>
	 */
	public function doGarbageCollection() : Future;

	/**
	 * Returns information about the world
	 * @return Future<WorldData>
	 */
	public function getWorldData() : Future;

	/**
	 * @return Future<void>
	 */

	public function reloadWorldData() : Future;

	/**
	 * Returns the number of chunks in the provider. Used for world conversion time estimations.
	 * @return Future<void>
	 */
	public function calculateChunkCount() : Future;
}
