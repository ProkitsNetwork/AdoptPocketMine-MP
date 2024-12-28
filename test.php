<?php

use pocketmine\world\format\io\ChunkData;
use pocketmine\world\format\SubChunk;

require_once __DIR__ . '/vendor/autoload.php';

$c = new ChunkData(
	[new SubChunk(0, [], new \pocketmine\world\format\PalettedBlockArray(1))], true, [], []
);
var_dump($c);
$x = (igbinary_serialize($c));
$b = igbinary_unserialize($x);
var_dump($b);