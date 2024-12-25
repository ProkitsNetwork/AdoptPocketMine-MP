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

namespace pocketmine\world\format\io\leveldb;

use PDO;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function file_exists;
use function mkdir;
use function unlink;

class SQLiteLevelDB{
	public PDO $pdo;

	public function __construct(string $filename){
		$dbDir = Path::getDirectory($filename);
		if(!file_exists($dbDir)){
			mkdir($dbDir, 0777, true);
		}
		$this->pdo = new PDO("sqlite:$filename");
		$this->pdo->exec("CREATE TABLE IF NOT EXISTS kv_store (
                x INTEGER,
                y INTEGER,
                type TEXT,
                value TEXT,
                UNIQUE(x, y, type)
            )");
		$this->pdo->exec("VACUUM");
	}

	public static function destroy(string $filename) : void{
		if(file_exists($filename)){
			unlink($filename);
		}
	}

	public static function repair(string $filename) : void{
		$pdo = new PDO("sqlite:" . $filename);
		$pdo->exec("VACUUM");
	}

	public function get(int $x, int $y, string $type) : string|false{
		$stmt = $this->pdo->prepare("SELECT value FROM kv_store WHERE x = :x AND y = :y AND type = :type");
		$stmt->execute([':x' => $x, ':y' => $y, ':type' => $type]);
		$col = $stmt->fetchColumn();
		return $col === false ? false : (string) $col;
	}

	/**
	 * @return \Generator<array{int,int,string},string,void,void>
	 */
	public function getIterator() : \Generator{
		$statement = Utils::assumeNotFalse($this->pdo->query("SELECT x, y, type, value FROM kv_store"));
		foreach(Utils::assumeNotFalse($statement->fetchAll(PDO::FETCH_ASSOC)) as $col){
			if(isset($col['value'], $col['type'], $col['x'], $col['y'])){
				yield [(int) $col['x'], (int) $col['y'], (string) $col['type']] => (string) $col['value'];
			}
		}
	}

	public function compactRange(string $start, string $limit) : void{
		// This is a no-op for SQLite since it handles storage compacting internally.
	}

	public function close() : void{
		unset($this->pdo);
	}

	public function write(SQLiteLevelDBWriteBatch $write) : void{
		$this->pdo->beginTransaction();
		try{
			foreach($write->put as $operation){
				$this->set($operation['x'], $operation['y'], $operation['key_type'], $operation['value']);
			}
			foreach($write->delete as $operation){
				$this->delete($operation['x'], $operation['y'], $operation['key_type']);
			}
			$this->pdo->commit();
		}catch(\Exception $e){
			$this->pdo->rollBack();
			throw $e;
		}
	}

	public function set(int $x, int $y, string $type, string $value) : bool{
		$stmt = $this->pdo->prepare(
			"INSERT INTO kv_store (x, y, type, value) VALUES (:x, :y, :type, :value)
             ON CONFLICT(x, y, type) DO UPDATE SET value = excluded.value"
		);
		return $stmt->execute([':x' => $x, ':y' => $y, ':type' => $type, ':value' => $value]);
	}

	public function delete(int $x, int $y, string $type) : bool{
		return $this->pdo->prepare("DELETE FROM kv_store WHERE x = :x AND y = :y AND type = :type")
			->execute([':x' => $x, ':y' => $y, ':type' => $type]);
	}
}
