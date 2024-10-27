<?php

namespace YonisSavary\Sharp\Classes\Data\MigrationManagerDrivers;

use YonisSavary\Sharp\Classes\Data\MigrationManager;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

class SqliteDriver extends MigrationManager
{

    public function migrationWasMade(string $name): bool
    {
        return count($this->database->query(
            "SELECT name FROM `{}` WHERE name = {}
        ", [$this->getMigrationTableName(), $name])) != 0;
    }

    public function createMigrationTableIfInexistant()
    {
        return $this->database->query(
            "CREATE TABLE IF NOT EXISTS `{}` (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(200) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ", [$this->getMigrationTableName()]);
    }

    public function markMigrationAsDone(string $name)
    {
        $this->database->query("INSERT INTO `{}` (name) VALUES ({})", [$this->getMigrationTableName(), $name]);
    }

    public function listDoneMigrations(): array
    {
        return ObjectArray::fromArray(
            $this->database->query("SELECT name FROM `{}` ORDER BY created_at", [$this->getMigrationTableName()])
        )
        ->map(fn($x) => $x["name"])
        ->collect();
    }


    protected function startTransaction()
    {
        $this->database->exec("BEGIN TRANSACTION");
    }

    protected function commitTransaction()
    {
        $this->database->exec("COMMIT");
    }

    protected function rollbackTransaction()
    {
        $this->database->exec("ROLLBACK");
    }
}