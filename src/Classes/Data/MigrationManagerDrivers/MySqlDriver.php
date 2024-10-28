<?php

namespace YonisSavary\Sharp\Classes\Data\MigrationManagerDrivers;

use YonisSavary\Sharp\Classes\Data\MigrationManager;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

class MySqlDriver extends MigrationManager
{

    public function migrationWasMade(string $name): bool
    {
        $path = $this->adaptName($name);
        $migName = basename($path);

        return count($this->database->query(
            "SELECT name FROM `{}` WHERE name = {}
        ", [$this->getMigrationTableName(), $migName])) != 0;
    }

    public function createMigrationTableIfInexistant()
    {
        return $this->database->query(
            "CREATE TABLE IF NOT EXISTS `{}` (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(200) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ", [$this->getMigrationTableName()]);
    }

    public function markMigrationAsDone(string $name)
    {
        $path = $this->adaptName($name);
        $migName = basename($path);

        $this->database->query("INSERT INTO `{}` (name) VALUES ({})", [$this->getMigrationTableName(), $migName]);
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
        $this->database->exec("START TRANSACTION");
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