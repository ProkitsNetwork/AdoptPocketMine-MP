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

namespace pocketmine\world\generator\overworld\populator;

use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\overworld\biome\BiomeIds;
use pocketmine\world\generator\overworld\populator\biome\BiomePopulator;
use pocketmine\world\generator\overworld\populator\biome\BirchForestMountainsPopulator;
use pocketmine\world\generator\overworld\populator\biome\BirchForestPopulator;
use pocketmine\world\generator\overworld\populator\biome\DesertMountainsPopulator;
use pocketmine\world\generator\overworld\populator\biome\DesertPopulator;
use pocketmine\world\generator\overworld\populator\biome\FlowerForestPopulator;
use pocketmine\world\generator\overworld\populator\biome\ForestPopulator;
use pocketmine\world\generator\overworld\populator\biome\IcePlainsPopulator;
use pocketmine\world\generator\overworld\populator\biome\IcePlainsSpikesPopulator;
use pocketmine\world\generator\overworld\populator\biome\JungleEdgePopulator;
use pocketmine\world\generator\overworld\populator\biome\JunglePopulator;
use pocketmine\world\generator\overworld\populator\biome\MegaSpruceTaigaPopulator;
use pocketmine\world\generator\overworld\populator\biome\MegaTaigaPopulator;
use pocketmine\world\generator\overworld\populator\biome\PlainsPopulator;
use pocketmine\world\generator\overworld\populator\biome\RoofedForestPopulator;
use pocketmine\world\generator\overworld\populator\biome\SavannaMountainsPopulator;
use pocketmine\world\generator\overworld\populator\biome\SavannaPopulator;
use pocketmine\world\generator\overworld\populator\biome\SunflowerPlainsPopulator;
use pocketmine\world\generator\overworld\populator\biome\SwamplandPopulator;
use pocketmine\world\generator\overworld\populator\biome\TaigaPopulator;
use pocketmine\world\generator\Populator;
use ReflectionClass;
use function array_key_exists;
use function array_values;

class OverworldPopulator implements Populator{

	/** @var Populator[] */
	private array $biome_populators = []; // key = biomeId

	/**
	 * Creates a populator with biome populators for all vanilla overworld biomes.
	 */
	public function __construct(){
		$this->registerBiomePopulator(new BiomePopulator()); // defaults applied to all biomes
		$this->registerBiomePopulator(new PlainsPopulator());
		$this->registerBiomePopulator(new SunflowerPlainsPopulator());
		$this->registerBiomePopulator(new ForestPopulator());
		$this->registerBiomePopulator(new BirchForestPopulator());
		$this->registerBiomePopulator(new BirchForestMountainsPopulator());
		$this->registerBiomePopulator(new RoofedForestPopulator());
		$this->registerBiomePopulator(new FlowerForestPopulator());
		$this->registerBiomePopulator(new DesertPopulator());
		$this->registerBiomePopulator(new DesertMountainsPopulator());
		$this->registerBiomePopulator(new JunglePopulator());
		$this->registerBiomePopulator(new JungleEdgePopulator());
		$this->registerBiomePopulator(new SwamplandPopulator());
		$this->registerBiomePopulator(new TaigaPopulator());
		$this->registerBiomePopulator(new MegaTaigaPopulator());
		$this->registerBiomePopulator(new MegaSpruceTaigaPopulator());
		$this->registerBiomePopulator(new IcePlainsPopulator());
		$this->registerBiomePopulator(new IcePlainsSpikesPopulator());
		$this->registerBiomePopulator(new SavannaPopulator());
		$this->registerBiomePopulator(new SavannaMountainsPopulator());
		/*
		$this->registerBiomePopulator(new ExtremeHillsPopulator());
		$this->registerBiomePopulator(new ExtremeHillsPlusPopulator());
		$this->registerBiomePopulator(new MesaPopulator());
		$this->registerBiomePopulator(new MesaForestPopulator());
		$this->registerBiomePopulator(new MushroomIslandPopulator());
		$this->registerBiomePopulator(new OceanPopulator());
		*/
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$biome = $chunk->getBiomeId(8, 8, 8);
		if(array_key_exists($biome, $this->biome_populators)){
			$this->biome_populators[$biome]->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		}
	}

	private function registerBiomePopulator(BiomePopulator $populator) : void{
		$biomes = $populator->getBiomes();
		if($biomes === null){
			$biomes = array_values((new ReflectionClass(BiomeIds::class))->getConstants());
		}

		foreach($biomes as $biome){
			$this->biome_populators[$biome] = $populator;
		}
	}
}
