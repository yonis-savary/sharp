<?php

namespace YonisSavary\Sharp\Classes\Data\ModelGenerator;

use Exception;
use InvalidArgumentException;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Utils;

class ModelGenerator
{
    use Component;

    protected GeneratorDriver $driver;

    public static function getDefaultInstance()
    {
        $dbConfig = Configuration::getInstance()->get("database", []);

        $driver = match($dbConfig["driver"] ?? null) {
            "mysql" => MySQL::class,
            "sqlite" => SQLite::class,
            default => null
        };

        if (!$driver)
            throw new Exception("Cannot adapt [". $dbConfig["driver"] ."] database tables");

        return new self($driver);
    }

    public function __construct(string $driverClass, Database $connection=null)
    {
        $connection ??= Database::getInstance();

        if (!Utils::extends($driverClass, GeneratorDriver::class))
            throw new InvalidArgumentException("[$driverClass] does not extends ". GeneratorDriver::class);

        $this->driver = new $driverClass($connection);
    }

    public function generateAll(string $application, string $modelNamespace=null): void
    {
        if (!is_dir($application))
            throw new InvalidArgumentException("[$application] does not exists !");

        $this->driver->generateAll($application, $modelNamespace);
    }
}