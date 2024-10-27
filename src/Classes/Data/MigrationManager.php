<?php

namespace YonisSavary\Sharp\Classes\Data;

use RuntimeException;
use Throwable;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\Configurable;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\MigrationManagerDrivers\MySqlDriver;
use YonisSavary\Sharp\Classes\Data\MigrationManagerDrivers\SqliteDriver;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Utils;

abstract class MigrationManager
{
    use Component;
    use Configurable;

    protected Database $database;
    protected Storage $storage;

    protected ?Throwable $lastError = null;
    protected ?string $lastErrorFile = null;

    public static function getDefaultConfiguration(): array
    {
        return [
            "table-name" => "__sharp_app_migrations",
            "directory-name" => "Migrations"
        ];
    }

    public function __construct(
        Database $database,
        Storage $storage=null,
        Configuration $configuration=null
    )
    {
        $this->database = $database;

        $configuration ??= Configuration::getInstance();
        $this->loadConfiguration($configuration);

        if (!$storage)
        {
            $applications = $configuration->toArray("applications", []);
            if (!count($applications))
                throw new RuntimeException("Could not assume your application migration directory");

            $mainApplication = ObjectArray::fromArray($applications)
            ->find(fn($x) => (new Storage(Utils::relativePath($x)))->isDirectory("Models") )
            ?? $applications[0]
            ;

            $storage = (new Storage(Utils::relativePath($mainApplication)))
            ->getSubStorage($this->configuration["directory-name"]);
        }

        $this->storage = $storage;

        $this->createMigrationTableIfInexistant();

    }

    public function getMigrationTableName(): string
    {
        return $this->configuration["table-name"];
    }

    public function getLastError(): ?Throwable
    {
        return $this->lastError;
    }

    public function getLastErrorFile(): ?string
    {
        return $this->lastErrorFile;
    }

    public abstract function migrationWasMade(string $name): bool;

    public abstract function createMigrationTableIfInexistant();

    public abstract function markMigrationAsDone(string $name);

    public abstract function listDoneMigrations(): array;

    protected abstract function startTransaction();

    protected abstract function commitTransaction();

    protected abstract function rollbackTransaction();

    public function getStorage(): Storage
    {
        return $this->storage;
    }

    public function listAllMigrations(): array
    {
        return ObjectArray::fromArray($this->storage->listFiles())
        ->map(fn($file) => basename($file))
        ->collect();
    }

    public function executeMigration(string $name): bool
    {
        if ($this->migrationWasMade($name))
            return true;

        if (!str_ends_with($name, ".sql"))
            $name = "$name.sql";

        try
        {
            $this->startTransaction();

            if (!$this->storage->isFile($name))
                throw new RuntimeException("$name file does not exists in your migration directory");

            $this->database->exec($this->storage->read($name));
            $this->markMigrationAsDone($name);
            $this->commitTransaction();
            return true;
        }
        catch (Throwable $thrown)
        {
            $this->rollbackTransaction();
            $this->lastError  = $thrown;
            $this->lastErrorFile = $this->storage->path($name);
            return false;
        }
    }

    public function executeAllMigrations(): bool
    {
        $this->startTransaction();

        try
        {
            foreach ($this->storage->listFiles() as $file)
            {
                $name = basename($file);

                if ($this->migrationWasMade($name))
                    continue;

                $this->database->exec($this->storage->read($name));
                $this->markMigrationAsDone($name);
            }
            $this->commitTransaction();
        }
        catch (Throwable $thrown)
        {
            $this->rollbackTransaction();
            $this->lastError  = $thrown;
            $this->lastErrorFile = $file;
            return false;
        }

        return true;
    }

    public function createMigration(string $name): string
    {
        $filename = time() . "-" . $name .".sql";
        $path = $this->storage->path($filename);
        $this->storage->write($filename, "");
        return $path;
    }


    public static function getDefaultInstance(): static
    {
        $database = Database::getInstance();
        $databaseDriver = $database->driver ?? null;

        switch (strtolower($databaseDriver))
        {
            case "mysql":
                return new MySqlDriver($database);
                break;
            case "sqlite":
                return new SqliteDriver($database);
                break;
        }
        throw new RuntimeException("No migration driver found for [$databaseDriver] database");
    }
}