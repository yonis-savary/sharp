<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Data;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\MigrationManager;
use YonisSavary\Sharp\Classes\Data\MigrationManagerDrivers\SqliteDriver;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Classes\Env\Storage;

class MigrationManagerTest extends TestCase
{
    public function createManager(array $config=null, Database &$database=null): MigrationManager
    {
        $database = new Database("sqlite", null);

        $config = $config ? Configuration::fromArray($config): null;
        $uniqueStorage = Storage::getInstance()->getSubStorage(uniqid("migration-"));

        return new SqliteDriver($database, $uniqueStorage, $config);
    }

    public function test_createMigration()
    {
        $manager = $this->createManager();

        $firstMigName = $manager->createMigration("first-migration");
        $this->assertFileExists($firstMigName);
        $this->assertMatchesRegularExpression("/^\d+\-first-migration\.sql$/", basename($firstMigName));
        $this->assertEquals([basename($firstMigName)], $manager->listAllMigrations());


        $secondMigName = $manager->createMigration("second-migration");
        $this->assertFileExists($secondMigName);
        $this->assertMatchesRegularExpression("/^\d+\-second-migration\.sql$/", basename($secondMigName));
        $this->assertEquals([basename($firstMigName), basename($secondMigName)], $manager->listAllMigrations());
    }

    public function test_executeMigration()
    {
        $database = null;
        $manager = $this->createManager(null, $database);

        $path = $manager->createMigration("table-creation");
        file_put_contents($path, "CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT, login VARCHAR(100) NOT NULL UNIQUE);");
        $name = basename($path);

        $this->assertFalse($manager->executeMigration("inexistant-one"));
        $this->assertTrue($manager->executeMigration($name));
        $this->assertTrue($manager->executeMigration($name));
        $this->assertCount(1, $manager->listAllMigrations());
        $this->assertCount(1, $manager->listDoneMigrations());
        $this->assertTrue($database->hasTable("user"));
        $this->assertTrue($database->hasField("user", "id"));
        $this->assertCount(1, $database->query("SELECT * FROM `{}`", [$manager->getMigrationTableName()]));



        $path = $manager->createMigration("table-insertion");
        // Multi-query test
        file_put_contents($path, "INSERT INTO user (login) VALUES ('admin');\n INSERT INTO user (login) VALUES ('guest');");
        $name = basename($path);

        $this->assertCount(2, $manager->listAllMigrations());
        $this->assertCount(1, $manager->listDoneMigrations());

        $this->assertFalse($manager->executeMigration("inexistant-one"));
        $this->assertTrue($manager->executeMigration($name));
        $this->assertTrue($manager->executeMigration($name));
        $this->assertCount(2, $manager->listDoneMigrations());

        $this->assertCount(2, $database->query("SELECT * FROM user"));
        $this->assertCount(2, $database->query("SELECT * FROM `{}`", [$manager->getMigrationTableName()]));

    }

    public function test_executeAll()
    {
        $database = null;
        $manager = $this->createManager(null, $database);

        $path = $manager->createMigration("table-creation");
        file_put_contents($path, "CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT, login VARCHAR(100) NOT NULL UNIQUE);");

        $path = $manager->createMigration("table-insertion");
        file_put_contents($path, "INSERT INTO user (login) VALUES ('admin');\n INSERT INTO user (login) VALUES ('guest');");

        $this->assertCount(2, $manager->listAllMigrations());
        $this->assertCount(0, $manager->listDoneMigrations());

        $this->assertTrue($manager->executeAllMigrations());
        $this->assertCount(2, $manager->listDoneMigrations());

        $this->assertTrue($database->hasTable("user"));
        $this->assertTrue($database->hasField("user", "id"));
        $this->assertCount(2, $database->query("SELECT * FROM `{}`", [$manager->getMigrationTableName()]));
        $this->assertCount(2, $database->query("SELECT * FROM user"));
        $this->assertCount(2, $database->query("SELECT * FROM `{}`", [$manager->getMigrationTableName()]));

    }

    /**
     * Test that migration file names are sorted by their timestamp and not their name
     */
    public function test_migrations_are_correctly_sorted()
    {
        $manager = $this->createManager();

        $storage = $manager->getStorage();

        $storage->write("1700043087_mig_Z.sql", "");
        $storage->write("1730013087_mig_X.sql", "");
        $storage->write("1730042087_mig_B.sql", "");
        $storage->write("1730043087_mig_A.sql", "");

        $this->assertEquals([
            "1700043087_mig_Z.sql",
            "1730013087_mig_X.sql",
            "1730042087_mig_B.sql",
            "1730043087_mig_A.sql",
        ], $manager->listAllMigrations());
    }

    public function test_migration_rollback_on_fail()
    {
        $database = null;
        $manager = $this->createManager(null, $database);

        $firstTableFile = $manager->createMigration("first-table");
        file_put_contents($firstTableFile, "CREATE TABLE some_data (id INT);");

        $tableCreationFile = $manager->createMigration("table-creation");
        file_put_contents($tableCreationFile, "CREATE TABLE user IM_AN_ERROR");

        $tableInsertionFile = $manager->createMigration("table-insertion");
        file_put_contents($tableInsertionFile, "INSERT INTO user (login) VALUES ('admin');\n INSERT INTO user (login) VALUES ('guest');");

        $this->assertFalse($manager->executeAllMigrations());
        $this->assertFalse($database->hasTable("some_data"));
        $this->assertFalse($database->hasTable("user"));

        $this->assertTrue($manager->executeMigration($firstTableFile));
        $this->assertTrue($database->hasTable("some_data"));
        $this->assertFalse($manager->executeAllMigrations());
        $this->assertFalse($database->hasTable("user"));
    }
}