<?php

namespace YonisSavary\Sharp\Tests\Units\Core;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\AbstractAnimal;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\CanTalk;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\Duck;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\Fish;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\FlyingAnimalInterface;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\Human;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\Mallard;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\WalkingAnimalInterface;

class AutoloaderTest extends TestCase
{
    # public function test_initialize() { $this->assertTrue(true); }
    # public function test_loadApplication() {}
    # public function test_getLoadedApplications() { $this->assertTrue(true); }
    # public function test_addToList() { $this->assertTrue(true); }
    # public function test_getList() { $this->assertTrue(true); }
    # public function test_getClassesList() { $this->assertTrue(true); }
    # public function test_loadAutoloadCache() { $this->assertTrue(true); }
    # public function test_writeAutoloadCache() { $this->assertTrue(true); }

    public function test_projectRoot()
    {
        $this->assertEquals(realpath(__DIR__ . "/../../Root"), Autoloader::projectRoot());
    }

    public function test_filterClasses()
    {
        $this->assertCount(4, Autoloader::filterClasses(fn($class) => Utils::extends($class, AbstractAnimal::class)));
        $this->assertCount(2, Autoloader::filterClasses(fn($class) => Utils::implements($class, FlyingAnimalInterface::class)));
    }

    public function test_classesThatImplements()
    {
        $this->assertEquals([
            Duck::class,
            Mallard::class,
        ], Autoloader::classesThatImplements(FlyingAnimalInterface::class));

        $this->assertEquals([
            Duck::class,
            Human::class,
            Mallard::class,
        ], Autoloader::classesThatImplements(WalkingAnimalInterface::class));
    }

    public function test_classesThatExtends()
    {
        $this->assertEquals([
            Duck::class,
            Fish::class,
            Human::class,
            Mallard::class,
        ], Autoloader::classesThatExtends(AbstractAnimal::class));

        $this->assertEquals([
            Mallard::class
        ], Autoloader::classesThatExtends(Duck::class));
    }

    public function test_classesThatUses()
    {
        $this->assertEquals([
            Human::class
        ], Autoloader::classesThatUses(CanTalk::class));
    }
}