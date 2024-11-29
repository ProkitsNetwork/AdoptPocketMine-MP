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

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\object\tree\BigOakTree;
use pocketmine\world\generator\object\tree\GenericTree;
use pocketmine\world\generator\overworld\decorator\CactusDecorator;
use pocketmine\world\generator\overworld\decorator\DeadBushDecorator;
use pocketmine\world\generator\overworld\decorator\DoublePlantDecorator;
use pocketmine\world\generator\overworld\decorator\FlowerDecorator;
use pocketmine\world\generator\overworld\decorator\LakeDecorator;
use pocketmine\world\generator\overworld\decorator\MushroomDecorator;
use pocketmine\world\generator\overworld\decorator\PumpkinDecorator;
use pocketmine\world\generator\overworld\decorator\SugarCaneDecorator;
use pocketmine\world\generator\overworld\decorator\SurfaceCaveDecorator;
use pocketmine\world\generator\overworld\decorator\TallGrassDecorator;
use pocketmine\world\generator\overworld\decorator\TreeDecorator;
use pocketmine\world\generator\overworld\decorator\types\FlowerDecoration;
use pocketmine\world\generator\overworld\decorator\types\TreeDecoration;
use pocketmine\world\generator\overworld\decorator\UnderwaterDecorator;
use pocketmine\world\generator\Populator;
use function array_push;
use const PHP_INT_MIN;

class BiomePopulator implements Populator{

	/** @var TreeDecoration[] */
	protected static array $TREES;

	/** @var FlowerDecoration[] */
	protected static array $FLOWERS;

	public static function init() : void{
		static::initTrees();
		static::initFlowers();
	}

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(BigOakTree::class, 1),
			new TreeDecoration(GenericTree::class, 9)
		];
	}

	protected static function initFlowers() : void{
		self::$FLOWERS = [
			new FlowerDecoration(VanillaBlocks::DANDELION(), 2),
			new FlowerDecoration(VanillaBlocks::POPPY(), 1)
		];
	}

	protected LakeDecorator $water_lake_decorator;
	protected LakeDecorator $lava_lake_decorator;
	protected OrePopulator $ore_populator;
	protected UnderwaterDecorator $sand_patch_decorator;
	protected UnderwaterDecorator $clay_patch_decorator;
	protected UnderwaterDecorator $gravel_patch_decorator;
	protected DoublePlantDecorator $double_plant_decorator;
	protected TreeDecorator $tree_decorator;
	protected FlowerDecorator $flower_decorator;
	protected TallGrassDecorator $tall_grass_decorator;
	protected DeadBushDecorator $dead_bush_decorator;
	protected MushroomDecorator $brown_mushroom_decorator;
	protected MushroomDecorator $red_mushroom_decorator;
	protected SugarCaneDecorator $sugar_cane_decorator;
	protected PumpkinDecorator $pumpkin_decorator;
	protected CactusDecorator $cactus_decorator;
	protected SurfaceCaveDecorator $surface_cave_decorator;

	/** @var Populator[] */
	private array $in_ground_populators = [];

	/** @var Populator[] */
	private array $on_ground_populators = [];

	/**
	 * Creates a populator for lakes; dungeons; caves; ores; sand, gravel and clay patches; desert
	 * wells; and vegetation.
	 */
	public function __construct(){
		$this->water_lake_decorator = new LakeDecorator(VanillaBlocks::WATER()->getStillForm(), 4);
		$this->lava_lake_decorator = new LakeDecorator(VanillaBlocks::LAVA()->getStillForm(), 8, 8);
		$this->ore_populator = new OrePopulator();
		$this->sand_patch_decorator = new UnderwaterDecorator(VanillaBlocks::SAND());
		$this->clay_patch_decorator = new UnderwaterDecorator(VanillaBlocks::CLAY());
		$this->gravel_patch_decorator = new UnderwaterDecorator(VanillaBlocks::GRAVEL());
		$this->double_plant_decorator = new DoublePlantDecorator();
		$this->tree_decorator = new TreeDecorator();
		$this->flower_decorator = new FlowerDecorator();
		$this->tall_grass_decorator = new TallGrassDecorator();
		$this->dead_bush_decorator = new DeadBushDecorator();
		$this->brown_mushroom_decorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
		$this->red_mushroom_decorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());
		$this->sugar_cane_decorator = new SugarCaneDecorator();
		$this->pumpkin_decorator = new PumpkinDecorator();
		$this->cactus_decorator = new CactusDecorator();
		$this->surface_cave_decorator = new SurfaceCaveDecorator();

		array_push($this->in_ground_populators,
			$this->water_lake_decorator,
			$this->lava_lake_decorator,
			$this->surface_cave_decorator,
			$this->ore_populator,
			$this->sand_patch_decorator,
			$this->clay_patch_decorator,
			$this->gravel_patch_decorator
		);

		array_push($this->on_ground_populators,
			$this->double_plant_decorator,
			$this->tree_decorator,
			$this->flower_decorator,
			$this->tall_grass_decorator,
			$this->dead_bush_decorator,
			$this->brown_mushroom_decorator,
			$this->red_mushroom_decorator,
			$this->sugar_cane_decorator,
			$this->pumpkin_decorator,
			$this->cactus_decorator
		);

		$this->initPopulators();
	}

	protected function initPopulators() : void{
		$this->water_lake_decorator->setAmount(1);
		$this->lava_lake_decorator->setAmount(1);
		$this->surface_cave_decorator->setAmount(1);
		$this->sand_patch_decorator->setAmount(3);
		$this->sand_patch_decorator->setRadii(7, 2);
		$this->sand_patch_decorator->setOverridableBlocks(VanillaBlocks::DIRT(), VanillaBlocks::GRASS());
		$this->clay_patch_decorator->setAmount(1);
		$this->clay_patch_decorator->setRadii(4, 1);
		$this->clay_patch_decorator->setOverridableBlocks(VanillaBlocks::DIRT());
		$this->gravel_patch_decorator->setAmount(1);
		$this->gravel_patch_decorator->setRadii(6, 2);
		$this->gravel_patch_decorator->setOverridableBlocks(VanillaBlocks::DIRT(), VanillaBlocks::GRASS());

		$this->double_plant_decorator->setAmount(0);
		$this->tree_decorator->setAmount(PHP_INT_MIN);
		$this->tree_decorator->setTrees(...self::$TREES);
		$this->flower_decorator->setAmount(2);
		$this->flower_decorator->setFlowers(...self::$FLOWERS);
		$this->tall_grass_decorator->setAmount(1);
		$this->dead_bush_decorator->setAmount(0);
		$this->brown_mushroom_decorator->setAmount(1);
		$this->brown_mushroom_decorator->setDensity(0.25);
		$this->red_mushroom_decorator->setAmount(1);
		$this->red_mushroom_decorator->setDensity(0.125);
		$this->sugar_cane_decorator->setAmount(10);
		$this->cactus_decorator->setAmount(0);
	}

	/**
	 * Returns an array of biome ids or null if this populator targets all
	 * biomes.
	 *
	 * @return int[]|null
	 */
	public function getBiomes() : ?array{
		return null;
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$this->populateInGround($world, $random, $chunk_x, $chunk_z, $chunk);
		$this->populateOnGround($world, $random, $chunk_x, $chunk_z, $chunk);
	}

	protected function populateInGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		foreach($this->in_ground_populators as $populator){
			$populator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		}
	}

	protected function populateOnGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		foreach($this->on_ground_populators as $populator){
			$populator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		}
	}
}

BiomePopulator::init();
