#!/usr/bin/env bash

echo "--- Installing RakLib,RakLibIpc dependencies from local repositories."
echo "--- This allows you to perform integration tests using PocketMine-MP, without immediately publishing new versions of these libraries."

cp composer.json composer-local-raklib.json
cp composer.lock composer-local-raklib.lock

export COMPOSER=composer-local-raklib.json
composer config repositories.raklib path ../RakLib
composer config repositories.raklib-ipc path ../RakLibIpc

composer require prokits-network/raklib:*@dev prokits-network/raklib-ipc:*@dev

composer install

echo "--- Local dependencies have been successfully installed."
echo "--- This script does not modify composer.json. To go back to the original dependency versions, simply run 'composer install'."

