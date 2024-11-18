<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\CLI;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Core\Utils;

/**
 * Command line interface arguments implementation (parsing)
 */
class ArgsTest extends TestCase
{
    public function test_fromArray()
    {
        $args = Args::fromArray(["--long", "value", "value1"]);
        $this->assertInstanceOf(Args::class, $args);
    }


    public function test_dump()
    {
        $args = new Args("-a -b -c");
        $this->assertIsArray($args->dump());
        $this->assertTrue(Utils::isAssoc($args->dump()));
    }

    public function test_toString()
    {
        $args = new Args('-a -b -c --long="value1" -d value2');
        $this->assertEquals('-a -b -c --long="value1" -d value2', $args->toString());
    }

    public function test_count()
    {
        $args = new Args('-a -b -c --long="value1" -d value2');
        $this->assertEquals(6, $args->count());
    }

    public function test_values()
    {
        $args = new Args('-a -b -c --long="value1" -d value2');
        $this->assertEquals(["value1", "value2"], $args->values());
    }

    public function test_getOption()
    {
        $args = new Args('-a -b -c --long="value1" -d value2');

        $this->assertEquals(null, $args->getOption("a"));
        $this->assertEquals(null, $args->getOption("b"));
        $this->assertEquals(null, $args->getOption("c"));
        $this->assertEquals("value1", $args->getOption(null, "long"));
        $this->assertEquals(null, $args->getOption("d"));
        $this->assertEquals(false, $args->getOption("e"));

        $this->assertEquals(null, $args->getOption("-a"));
        $this->assertEquals(null, $args->getOption("-b"));
        $this->assertEquals(null, $args->getOption("-c"));
        $this->assertEquals("value1", $args->getOption(null, "--long"));
        $this->assertEquals(null, $args->getOption("-d"));
        $this->assertEquals(false, $args->getOption("-e"));
    }

    public function test_isPresent()
    {
        $args = new Args('-a -b -c --long="value1" -d value2');

        $this->assertTrue ($args->isPresent("a"));
        $this->assertTrue ($args->isPresent("b"));
        $this->assertTrue ($args->isPresent("c"));
        $this->assertTrue ($args->isPresent(null, "long"));
        $this->assertTrue ($args->isPresent("d"));
        $this->assertFalse($args->isPresent("e"));

        $this->assertTrue ($args->isPresent("-a"));
        $this->assertTrue ($args->isPresent("-b"));
        $this->assertTrue ($args->isPresent("-c"));
        $this->assertTrue ($args->isPresent(null, "--long"));
        $this->assertTrue ($args->isPresent("-d"));
        $this->assertFalse($args->isPresent("-e"));
    }

    public function test_get()
    {
        $args = new Args('-a -b -c --long="value1" -d value2');

        $this->assertEquals(null, $args->get("a"));
        $this->assertEquals(null, $args->get("b"));
        $this->assertEquals(null, $args->get("c"));
        $this->assertEquals("value1", $args->get(null, "long"));
        $this->assertEquals(null, $args->get("d"));
        $this->assertEquals(null, $args->get("e"));

        $this->assertEquals(null, $args->get("-a"));
        $this->assertEquals(null, $args->get("-b"));
        $this->assertEquals(null, $args->get("-c"));
        $this->assertEquals("value1", $args->get(null, "--long"));
        $this->assertEquals(null, $args->get("-d"));
        $this->assertEquals(null, $args->get("-e"));
    }
}