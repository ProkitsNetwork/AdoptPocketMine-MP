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

namespace pocketmine\updater;

use pocketmine\utils\Internet;
use pocketmine\utils\Utils;
use function json_decode;
use function parse_url;
use function preg_match;
use function str_ends_with;
use function substr;
use function substr_count;
use function trim;
use const JSON_THROW_ON_ERROR;

class UpdaterApi{
	private const TIMEOUT = 3;

	public static function retrieve(
		string $repo = "ProkitsNetwork/AdoptPocketMine-MP",
		string $protocolPackage = "nethergamesmc/bedrock-protocol",
		string $channel = "stable"
	) : UpdateInfo{
		$url = "https://api.github.com/repos/$repo/branches/$channel";
		$result = Internet::getURL($url, self::TIMEOUT);
		if($result === null || $result->getCode() !== 200){
			throw new \RuntimeException("Failed to communicate with GitHub when retrieving latest commit. ");
		}
		$latest = json_decode($result->getBody(), true, 16, JSON_THROW_ON_ERROR);
		if(!is_array($latest)){
			throw new \RuntimeException("Malformed commit data retrieved from $url");
		}

		$latestCommit = (string) ($latest["commit"]["sha"] ?? throw new \RuntimeException("invalid commit data (missing commit sha)."));

		$baseVersion = self::fetchBaseVersion($repo, $latestCommit);

		$info = new UpdateInfo();

		$info->channel = $channel;
		$info->base_version = $baseVersion;
		$info->is_dev = true;
		$info->details_url = "https://github.com/$repo/tree/$latestCommit";
		$info->download_url = "https://codeload.github.com/$repo/zip/$latestCommit";
		$info->source_url = "https://github.com/$repo/tree/$latestCommit";
		$date = $latest["commit"]["commit"]["committer"]["date"] ?? $latest["commit"]["commit"]["author"]["date"] ?? throw new \RuntimeException("Failed to parse commit time");
		$info->date = Utils::assumeNotFalse(\DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, (string) $date))->getTimestamp();
		$info->mcpe_version = self::fetchProtocolVersion($protocolPackage, $repo, $latestCommit);
		$info->git_commit = $latestCommit;
		$info->build = -1;
		$info->php_version = 'N/A'; // Assuming this is a default value, but it should be properly assigned based on actual data.
		return $info;
	}

	private static function fetchBaseVersion(string $repo, string $commit) : string{
		$req = Internet::getURL("https://raw.githubusercontent.com/$repo/$commit/src/VersionInfo.php");
		if($req === null || $req->getCode() !== 200){
			throw new \RuntimeException("Failed to communicate with GitHub when fetching base version");
		}
		$versionInfo = $req->getBody();

		if(preg_match('/const BASE_VERSION = "(.*?)";/', $versionInfo, $matches) === false){
			throw new \RuntimeException("BASE_VERSION not found in VersionInfo.php");
		}

		$baseVersion = $matches[1];
		if(preg_match('/^(\d+)\.(\d+)\.(\d+)(?:-(.*))?$/', $baseVersion) === false){
			throw new \RuntimeException("Invalid BASE_VERSION format");
		}

		return $baseVersion;
	}

	private static function fetchProtocolVersion(string $packageName, string $repo, string $commit) : string{
		$composerLockUrl = "https://raw.githubusercontent.com/$repo/$commit/composer.lock";
		$composerLock = Internet::getURL($composerLockUrl)?->getBody();
		if($composerLock === null){
			throw new \RuntimeException("Failed to retrieve composer.lock");
		}
		$composerLock = json_decode($composerLock, true, 512, JSON_THROW_ON_ERROR);
		if(!is_array($composerLock)){
			throw new \RuntimeException("Invalid composer.lock received from $composerLockUrl.");
		}
		foreach($composerLock["packages"] as $package){
			if(!is_array($package)){
				throw new \RuntimeException("Invalid composer package received from $composerLockUrl.");
			}
			if($package["name"] === $packageName){
				$repoUrl = $package["source"]["url"];
				$protocolRepo = self::parseRepo($repoUrl);
				$protocolCommit = $package["source"]["reference"];
				$protocolInfo = Internet::getURL("https://raw.githubusercontent.com/$protocolRepo/$protocolCommit/src/ProtocolInfo.php")?->getBody();
				if($protocolInfo === null){
					throw new \RuntimeException("Failed to retrieve ProtocolInfo.php");
				}
				if(preg_match('/const MINECRAFT_VERSION_NETWORK = \'(.*?)\';/', $protocolInfo, $matches) === false){
					throw new \RuntimeException("MINECRAFT_VERSION_NETWORK not found in ProtocolInfo.php");
				}
				return $matches[1];
			}
		}
		throw new \RuntimeException("Package $packageName not found in composer.lock");
	}

	private static function parseRepo(string $url) : string{
		$parsedUrl = parse_url($url);
		if($parsedUrl === false || !isset($parsedUrl['host']) || $parsedUrl['host'] !== 'github.com' || !isset($parsedUrl['path'])){
			throw new \RuntimeException("Invalid repository host in URL: $url");
		}

		$path = trim($parsedUrl['path'], '/');
		if(!str_ends_with($path, '.git')){
			throw new \RuntimeException("Repository path does not end with .git in URL: $url");
		}
		$path = substr($path, 0, -4);

		if(substr_count($path, '/') + 1 !== 2){
			throw new \RuntimeException("Invalid repository path format in URL: $url");
		}
		return $path;
	}
}
