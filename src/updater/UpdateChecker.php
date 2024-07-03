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

use pocketmine\event\server\UpdateNotifyEvent;
use pocketmine\Server;
use pocketmine\utils\VersionString;
use pocketmine\VersionInfo;
use pocketmine\YmlServerProperties;
use function date;
use function strtolower;
use function time;
use function ucfirst;

class UpdateChecker{

	protected Server $server;
	protected ?UpdateInfo $updateInfo = null;
	private \Logger $logger;

	public function __construct(Server $server){
		$this->server = $server;
		$this->logger = new \PrefixedLogger($server->getLogger(), "Update Checker");

		if($server->getConfigGroup()->getPropertyBool(YmlServerProperties::AUTO_UPDATER_ENABLED, true)){
			$this->doCheck();
		}
	}

	public function checkUpdateError(string $error) : void{
		$this->logger->debug("Async update check failed due to \"$error\"");
	}

	/**
	 * Callback used at the end of the update checking task
	 */
	public function checkUpdateCallback(UpdateInfo $updateInfo) : void{
		$this->checkUpdate($updateInfo);
		if($this->hasUpdate()){
			(new UpdateNotifyEvent($this))->call();
			if($this->server->getConfigGroup()->getPropertyBool(YmlServerProperties::AUTO_UPDATER_ON_UPDATE_WARN_CONSOLE, true)){
				$this->showConsoleUpdate();
			}
		}elseif($this->server->getConfigGroup()->getPropertyBool(YmlServerProperties::AUTO_UPDATER_SUGGEST_CHANNELS, true)){
			if(!VersionInfo::IS_DEVELOPMENT_BUILD && $this->getBranch() !== "stable"){
				$this->showChannelSuggestionStable();
			}elseif(VersionInfo::IS_DEVELOPMENT_BUILD && $this->getBranch() === "stable"){
				$this->showChannelSuggestionBeta();
			}
		}
	}

	/**
	 * Returns whether there is an update available.
	 */
	public function hasUpdate() : bool{
		return $this->updateInfo !== null;
	}

	/**
	 * Posts a warning to the console to tell the user there is an update available
	 */
	public function showConsoleUpdate() : void{
		if($this->updateInfo === null){
			return;
		}
		$newVersion = new VersionString($this->updateInfo->base_version, $this->updateInfo->is_dev, $this->updateInfo->build);
		$messages = [
			"Your version of " . $this->server->getName() . " is out of date. Version " . $newVersion->getFullVersion(true) . " was released on " . date("D M j h:i:s Y", $this->updateInfo->date)
		];

		$messages[] = "Details: " . $this->updateInfo->details_url;
		$messages[] = "Download: " . $this->updateInfo->download_url;

		$this->printConsoleMessage($messages, \LogLevel::WARNING);
	}

	protected function showChannelSuggestionStable() : void{
		$this->printConsoleMessage([
			"You're running a Stable build, but you're receiving update notifications for " . ucfirst($this->getBranch()) . " builds.",
			"To get notified about new Stable builds only, change 'preferred-channel' in your pocketmine.yml to 'stable'."
		]);
	}

	protected function showChannelSuggestionBeta() : void{
		$this->printConsoleMessage([
			"You're running a Beta build, but you're receiving update notifications for Stable builds.",
			"To get notified about new Beta or Development builds, change 'preferred-channel' in your pocketmine.yml to 'beta' or 'development'."
		]);
	}

	/**
	 * @param string[] $lines
	 */
	protected function printConsoleMessage(array $lines, string $logLevel = \LogLevel::INFO) : void{
		foreach($lines as $line){
			$this->logger->log($logLevel, $line);
		}
	}

	/**
	 * Returns the last retrieved update data.
	 */
	public function getUpdateInfo() : ?UpdateInfo{
		return $this->updateInfo;
	}

	/**
	 * Schedules an AsyncTask to check for an update.
	 */
	public function doCheck() : void{
		$this->server->getAsyncPool()->submitTask(new UpdateCheckTask($this, $this->getBranch()));
	}

	/**
	 * Checks the update information against the current server version to decide if there's an update
	 */
	protected function checkUpdate(UpdateInfo $updateInfo) : void{
		if(VersionInfo::GIT_HASH() !== $updateInfo->git_commit){
			if($updateInfo->date <= VersionInfo::GIT_COMMIT_DATE()){
				$this->logger->debug("API reported version is an older version, not showing notification");
			}else{
				$this->updateInfo = $updateInfo;
			}
		}else{
			$this->logger->debug("API reported version is the same version, not showing notification");
		}
	}

	/**
	 * Returns the branch used for update checking
	 */
	public function getBranch() : string{
		return strtolower($this->server->getConfigGroup()->getPropertyString(YmlServerProperties::AUTO_UPDATER_BRANCH, "stable"));
	}
}
