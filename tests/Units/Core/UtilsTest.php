<?php

namespace YonisSavary\Sharp\Tests\Units\Core;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Configuration\ApplicationsToLoad;
use YonisSavary\Sharp\Core\Configuration\Environmnent;
use YonisSavary\Sharp\Core\Utils;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\AbstractAnimal;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\CanTalk;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\Duck;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\Fish;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\FlyingAnimalInterface;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\Human;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\Mallard;
use YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals\SwimingAnimalInterface;
use YonisSavary\Sharp\Tests\Units\TestClassFactory;

/**
 * This class holds utilities statical methods that can be reused
 */
class UtilsTest extends TestCase
{
    public function test_uses()
    {
        $this->assertTrue (Utils::uses(Human::class, CanTalk::class));
        $this->assertFalse(Utils::uses(Duck::class, CanTalk::class));
        $this->assertFalse(Utils::uses(Fish::class, CanTalk::class));
    }

    public function test_implements()
    {
        $this->assertTrue(Utils::implements(Human::class, SwimingAnimalInterface::class));
        $this->assertFalse(Utils::implements(Human::class, FlyingAnimalInterface::class));

        $this->assertTrue(Utils::implements(Duck::class, SwimingAnimalInterface::class));
        $this->assertTrue(Utils::implements(Duck::class, FlyingAnimalInterface::class));
    }

    public function test_extends()
    {
        $this->assertTrue(Utils::extends(Human::class, AbstractAnimal::class));

        $this->assertTrue(Utils::extends(Duck::class, AbstractAnimal::class));
        $this->assertTrue(Utils::extends(Mallard::class, AbstractAnimal::class));
        $this->assertTrue(Utils::extends(Mallard::class, Duck::class));

        $this->assertFalse(Utils::extends(Human::class, Duck::class));
    }

    public function test_normalizePath()
    {
        $this->assertEquals('domain/class.php', Utils::normalizePath('domain/class.php'));
        $this->assertEquals('domain/class.php', Utils::normalizePath('domain//class.php'));
        $this->assertEquals('domain/class.php', Utils::normalizePath("domain\\class.php"));
        $this->assertEquals('domain/class.php', Utils::normalizePath("domain\\\\class.php"));
    }

    public function test_joinPath()
    {
        $this->assertEquals('domain/class.php', Utils::joinPath('domain', 'class.php'));
        $this->assertEquals('domain/class.php', Utils::joinPath('domain/', 'class.php'));
        $this->assertEquals('domain/class.php', Utils::joinPath('domain', '\class.php'));
        $this->assertEquals('domain/class.php', Utils::joinPath('domain/', '/class.php'));
        $this->assertEquals('domain/class.php', Utils::joinPath('domain/', '\class.php'));
    }

    public function test_relativePath()
    {
        $this->assertEquals(
            Utils::joinPath(Autoloader::projectRoot(), 'domain/class.php'),
            Utils::relativePath('domain/class.php')
        );
    }

    public function test_pathToNamespace()
    {
        $this->assertEquals(
            Utils::classnameToPath('Domain\Subdomain\Class'),
            Utils::relativePath('Domain/Subdomain/Class.php')
        );
    }

    public function test_classnameToPath()
    {
        $this->assertEquals(
            Utils::relativePath('Domain/Subdomain/Class.php'),
            Utils::classnameToPath('Domain\Subdomain\Class')
        );
    }

    public function test_exploreDirectory()
    {
        TestClassFactory::withDummyStorage(function(Storage $_, string $path) {
            $this->assertCount(6, Utils::exploreDirectory($path));
            $this->assertCount(5, Utils::exploreDirectory($path, Utils::ONLY_FILES));
            $this->assertCount(1, Utils::exploreDirectory($path, Utils::ONLY_DIRS));
        }, [
            "a.txt",
            "b.txt",
            "c.txt",
            "dir/a.txt",
            "dir/b.txt",
        ]);
    }

    public function test_listFiles()
    {
        TestClassFactory::withDummyStorage(function(Storage $_, string $path) {
            $this->assertCount(3, Utils::listFiles($path));
        }, [
            "a.txt",
            "b.txt",
            "c.txt",
            "dir/a.txt",
            "dir/b.txt",
        ]);
    }

    public function test_listDirectories()
    {
        TestClassFactory::withDummyStorage(function(Storage $_, string $path) {
            $this->assertCount(1, Utils::listDirectories($path));
        }, [
            "a.txt",
            "b.txt",
            "c.txt",
            "dir/a.txt",
            "dir/b.txt",
        ]);
    }


    public function test_valueHasFlag()
    {
        $this->assertFalse(Utils::valueHasFlag(0b1010_1010, 0b0000_0001));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b0000_0010));
        $this->assertFalse(Utils::valueHasFlag(0b1010_1010, 0b0000_0100));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b0000_1000));
        $this->assertFalse(Utils::valueHasFlag(0b1010_1010, 0b0001_0000));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b0010_0000));
        $this->assertFalse(Utils::valueHasFlag(0b1010_1010, 0b0100_0000));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b1000_0000));

        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b0000_1010));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b1010_0000));

        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b0000_0000));
        $this->assertTrue (Utils::valueHasFlag(0b1010_1010, 0b1010_1010));
    }

    public function test_isAssoc()
    {
        $this->assertTrue(Utils::isAssoc(['A' => 5]));
        $this->assertFalse(Utils::isAssoc([['A' => 5]]));
        $this->assertFalse(Utils::isAssoc([]));

        $this->assertFalse(Utils::isAssoc([1, 2, 3]));
        $this->assertFalse(Utils::isAssoc(['A', 'B', 'C']));
    }

    public function test_toArray()
    {
        $this->assertEquals([5], Utils::toArray(5));
        $this->assertEquals([5], Utils::toArray([5]));
        $this->assertEquals([['A' => 5]], Utils::toArray(['A'=>5]));
        $this->assertEquals([['A' => 5]], Utils::toArray([['A'=>5]]));
    }

    public function test_isProduction()
    {
        foreach ([
            "prod",
            "production",
            "PRODUCTION"
        ] as $env){
            $configInProduction = new Environmnent($env);
            $this->assertTrue(Utils::isProduction($configInProduction));
        }

        $configInDebug = new Environmnent("debug");
        $this->assertFalse(Utils::isProduction($configInDebug));
    }

    public function test_isApplicationEnabled()
    {
        $dummyConfig = new ApplicationsToLoad(['TestApp']);

        $this->assertTrue(Utils::isApplicationEnabled('TestApp', $dummyConfig));
        $this->assertFalse(Utils::isApplicationEnabled('OtherApp', $dummyConfig));
    }

    public function test_randomHexString()
    {
        $this->expectException(InvalidArgumentException::class);
        Utils::randomHexString(-1);
        $this->expectException(InvalidArgumentException::class);
        Utils::randomHexString(0);

        for ($i=1; $i<100; $i++)
            $this->assertTrue(strlen(Utils::randomHexString($i)) == $i);
    }

}