<?php

namespace YonisSavary\Sharp\Tests\Units;

use Exception;
use RuntimeException;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Security\Authentication;
use YonisSavary\Sharp\Core\Utils;

class TestClassFactory
{
    public static function createDatabase(): Database
    {
        $newDB = new Database('sqlite', null);

        $schemaPath = Utils::relativePath('TestApp/schema.sql');
        if (!is_file($schemaPath))
            throw new Exception("$schemaPath file does not exists !");

        $schema = file_get_contents($schemaPath);
        $schema = ObjectArray::fromExplode(';', $schema)
        ->map(trim(...))
        ->filter()
        ->collect();

        foreach ($schema as $query)
            $newDB->query($query);

        return $newDB;
    }

    public static function createAuthentication(): Authentication
    {
        return new Authentication(database: self::createDatabase());
    }


    public static function withDummyStorage(callable $callback, array $files=[])
    {
        $globalStorage = Storage::getInstance();
        $id = uniqid("dummy-storage-");

        if ($globalStorage->isDirectory($id))
            throw new RuntimeException("Could not create dummy storage $id as it already exists");

        $storage = $globalStorage->getSubStorage($id);
        $storageRoot = $storage->getRoot();

        foreach ($files as $file)
            $storage->write($file, "");

        $callback($storage, $storageRoot);

        foreach (array_reverse($storage->exploreDirectory("/", Utils::ONLY_FILES)) as $file)
            unlink($file);
        foreach (array_reverse($storage->exploreDirectory("/", Utils::ONLY_DIRS)) as $file)
            rmdir($file);

        rmdir($storage->getRoot());
        unset($storage);
    }
}