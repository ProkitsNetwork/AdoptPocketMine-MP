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

namespace pocketmine\network\mcpe\convert;

use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use pocketmine\utils\Utils;
use function base64_decode;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class LegacySkinAdapter implements SkinAdapter{
	private const FALLBACK_SKIN = 'eNrsmt1rXEUYxvdSkyY2RTEk/dJt4zFt2cbGiyZoamO/RBQTKtqVtjRiY9IQWKqCRRq12EahehU4Za0gCIsovfDrQuKlV/s/vfLMOe/xPcPMmexHkrObeeHhTGbemT2/+drZvFNw2Oi+PoIODT5GMg0d2N1LH50rZarQ4ca8pQNPKEl+CIwLp47TV7PjyRPqJn5mHxnqsfIXi8WUkNcN/Dz2+hrgvG7nl6wYf0iuhZ3Ab2J38c8EI13Dz9zP792l0tj3dX6TuoWfuaV4XnT6918w3EMQmJ4b7lWsLxR307GDkUoH++jI/mgPCPb2qj0QvsgDP/uhDuqiDZRzu67zQ574oaP7+xUH+E4cGlBPFv4+PbqHpkafUn7oD/ZBHeRxO5I/6/yw3fzqnQejMYu4+ykYxjj3JP3w5+oNWr9foX/XVmj9mwqt379Jv3x8icYP71E+8EWdI3HfqT4Y7FX94To/5GL8h/ro2L7+aEyHo3WAdOnZAfr5sw/o9ztL6vnryhJVK+/R36sVqn74uhJ84Is6qIs02kKb+Nt1fsjL/I/WwC4qPv043X1ngqpzZ2gieFKxw2ZeWqU/bl1X7EjD0CfwgS/qoC7akG26zg+54R/qi+fsAN2ZGaO1K6/QvUsnqTp3ln67dY3++qKSfK/9c+8G/bhwjlZmx5UPfFEHddEGj73c/2znhzysf7wn5uy16RJ9v/AmPZh7NfkN893laVq7coo+Of1iSt9efpk+nz2pfOCLOqiLNtT8j9eC6/yw3fz8e42Zq9fPq/H8+t1Junm+RMtnjtLS9KhihT594wR9eXFSCWn4wPeH+Quq7oP3X4uecXvcj/r5gfstb+eBty/USSoIgpScDdTr9rNQvU6Fq1ez5bJaLWoHqtXUenxr7HDyzAO/7TzcFn5mj7Wj+Wu1FLv6jB02/1vlPx5UCBp5pqyeZyd+SkmW6eUTY6tUuH37f5XLVAhDKjx8qPZKfEfyvok8JekPIQ9MrEePIvHfNn/20cu5PmsD/OBrhh9Snzk/Hwlpfu+4DxJ2fh/pD0lmKW5H+jIf1+H+1Mub4LfxOflN78fviPfDfJCM0h8yscs2TP0rfbabH3OePx9p+dnML/Ns/uwHPpnW+eRc2qLxlz5Y81IJBwvvLpk5zfnSl/l1ZlEfZzHW3YuT6jeHFPKkj17eDD+zyfG38ksW9IXkN3HJ/uL5b6oTz3MT/9pyOeHj/HbyN7z+dX459nKvlvxy/WvMsj8km86Pp4lf5cc+G/3+a5pfX38ufn3/N/la+PXx1flN66Md/FnlmfuPvvfr/Kb9SpNks/Fn7Q8u/qmpKYJc/Oy3uLiYUsLD5w8eO33sbfzS37AXSDbT+pb8cv7zc6P8Nj5XuVwfeOr9l5wTBb9+3kr2Ul4jYZjsvzZ+ZjTNj+3iN82fVB+Eofs8HYapPH18U3vfctm4PzTC782bN2/evHnz5s2bN2/evHnz1qi1HD9tc3yz4/i1+P6O45fjvwn3Gzpt/uedv9H7A/x/3VT8QEr+z9x0X6DB+GZe+G3l1vgu94MtXtRB/FnlmfEgU/y72/nbHN/fCv5W7g9sdnx/q/hN8axG+F3xrTzztzL/dX4Zu9L5Zfx/o/H9vPDb1ocev5R3O/T4bjPx7a3ib/b+gD6/Tfyt3G/YbGv1/oApfm/jN62PvPA3Gz83jW/W/aZu5E/tgWJv0++3bAb/fwEAAP//JeKX1w==';
	private static string $fallback;

	public function __construct(){
		if(!isset(self::$fallback)){
			self::$fallback = ZlibCompressor::getInstance()->decompress(Utils::assumeNotFalse(base64_decode(self::FALLBACK_SKIN, true)));
		}
	}

	public function toSkinData(Skin $skin) : SkinData{
		$capeData = $skin->getCapeData();
		$capeImage = $capeData === "" ? new SkinImage(0, 0, "") : new SkinImage(32, 64, $capeData);
		$geometryName = $skin->getGeometryName();
		if($geometryName === ""){
			$geometryName = "geometry.humanoid.custom";
		}
		return new SkinData(
			$skin->getSkinId(),
			"", //TODO: playfab ID
			json_encode(["geometry" => ["default" => $geometryName]], JSON_THROW_ON_ERROR),
			SkinImage::fromLegacy($skin->getSkinData()), [],
			$capeImage,
			$skin->getGeometryData()
		);
	}

	public function fromSkinData(SkinData $data) : Skin{
		if($data->isPersona()){
			return new Skin("Standard_Custom", self::$fallback);
		}

		$capeData = $data->isPersonaCapeOnClassic() ? "" : $data->getCapeImage()->getData();

		$resourcePatch = json_decode($data->getResourcePatch(), true);
		if(is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])){
			$geometryName = $resourcePatch["geometry"]["default"];
		}else{
			throw new InvalidSkinException("Missing geometry name field");
		}

		return new Skin($data->getSkinId(), $data->getSkinImage()->getData(), $capeData, $geometryName, $data->getGeometryData());
	}
}
