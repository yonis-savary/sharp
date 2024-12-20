<?php

namespace YonisSavary\Sharp\Classes\Data;

use RuntimeException;
use Throwable;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Data\Configuration\MigrationManagerConfiguration;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\MigrationManagerDrivers\MySqlDriver;
use YonisSavary\Sharp\Classes\Data\MigrationManagerDrivers\SqliteDriver;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Configuration\ApplicationsToLoad;
use YonisSavary\Sharp\Core\Utils;

abstract class MigrationManager
{
    use Component;

    protected Database $database;
    protected Storage $storage;

    protected ?Throwable $lastError = null;
    protected ?string $lastErrorFile = null;

    protected MigrationManagerConfiguration $configuration;

    public function __construct(
        Database $database,
        Storage $storage=null,
        MigrationManagerConfiguration $configuration=null
    )
    {
        $this->database = $database;

        $this->configuration ??= MigrationManagerConfiguration::resolve();

        if (!$storage)
        {
            $applications = ApplicationsToLoad::resolve()->applications;
            if (!count($applications))
                throw new RuntimeException("Could not assume your application migration directory");

            $mainApplication = ObjectArray::fromArray($applications)
            ->find(fn($x) => (new Storage(Utils::relativePath($x)))->isDirectory("Models") )
            ?? $applications[0]
            ;

            $storage = (new Storage(Utils::relativePath($mainApplication)))
            ->getSubStorage($this->configuration->directoryName);
        }

        $this->storage = $storage;

        $this->createMigrationTableIfInexistant();

    }

    public function getMigrationTableName(): string
    {
        return $this->configuration->tableName;
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
        if (!str_ends_with($name, ".sql"))
            $name = "$name.sql";

        if ($this->migrationWasMade($name))
            return true;

        try
        {
            $this->startTransaction();

            if (! $target = $this->adaptName($name))
                throw new RuntimeException("$name file does not exists in your migration directory");

            $sqlContent = $this->storage->read($target);
            if (!trim($sqlContent))
                Logger::getInstance()->warning("Skipping empty migration $target");
            else
                $this->database->exec($sqlContent);

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

                $sqlContent = $this->storage->read($name);
                if (!trim($sqlContent))
                    Logger::getInstance()->warning("Skipping empty migration $file");
                else
                    $this->database->exec($sqlContent);

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
        $filename = time() . "_" . $name .".sql";
        $path = $this->storage->path($filename);
        $this->storage->write($filename, "");
        return $path;
    }

    public function migrationExists(string $name): bool
    {
        try
        {
            $path = $this->adaptName($name);
            return is_file($path);
        }
        catch (Throwable $e)
        {
            return false;
        }
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

    public function adaptName(string $name): string
    {
        if (!str_ends_with($name, ".sql"))
            $name .= ".sql";

        if (is_file($name))
            return $name;

        if ($this->storage->isFile($name))
            return $this->storage->path($name);

        foreach ($this->storage->listFiles() as $file)
        {
            $filename = basename($file);
            if (!preg_match("/^\d+_.+$/", $filename))
                continue;

            list($timestamp, $currentName) = explode("_", $filename, 2);

            if ($name === $currentName)
                return $file;
        }

        throw new RuntimeException("Could not adapt [$name] into a migration_name");
    }

    public function catchUpTo(string $name): array
    {
        $target = $this->adaptName($name);
        $doneMigrations = [];

        foreach ($this->storage->listFiles() as $file)
        {
            $migName = $this->adaptName($file);
            $doneMigrations[] = $file;

            if (!$this->migrationWasMade($migName))
                $this->markMigrationAsDone($migName);

            if ($file === $target)
                break;
        }
        return $doneMigrations;
    }
}