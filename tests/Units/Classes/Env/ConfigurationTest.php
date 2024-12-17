<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Env;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Env\Configuration\Configuration;
use YonisSavary\Sharp\Classes\Env\Configuration\GenericConfiguration;
use YonisSavary\Sharp\Classes\Http\Configuration\RequestConfiguration;
use YonisSavary\Sharp\Classes\Security\Configuration\CsrfConfiguration;
use YonisSavary\Sharp\Classes\Web\Configuration\RouterConfiguration;

class ConfigurationTest extends TestCase
{
    public function test_mergeWithFile()
    {
        $config = new Configuration();
        $config->mergeWithFile("another-config.php");

        $this->assertEquals(
            "Hello",
            $config->resolveByName("custom-element")["word"]
        );
    }

    public function test_resolve()
    {
        $config = new Configuration();
        $config->addElements(
            new CsrfConfiguration("one"),
            new CsrfConfiguration("two"),
            new RequestConfiguration(false),
            new CsrfConfiguration("three"),
        );

        $this->assertEquals("one", $config->resolve(CsrfConfiguration::class, false)->htmlInputName);
        $this->assertEquals(false, $config->resolve(RequestConfiguration::class, false)->typedParameters);
        $this->assertFalse($config->resolve(RouterConfiguration::class, false));
    }

    public function test_resolveByName()
    {
        $config = new Configuration();
        $config->addElements(
            new GenericConfiguration("hello", "Hello world !"),
            new GenericConfiguration("goodbye", "Goodbye world !"),
        );

        $this->assertEquals("Hello world !", $config->resolveByName("hello"));
        $this->assertEquals("Goodbye world !", $config->resolveByName("goodbye"));
        $this->assertFalse($config->resolveByName("farewell", false));
    }

    public function test_importAnotherConfig()
    {
        $config = Configuration::fromFile("complex-config.php");

        debug(
            $config->getElements()
        );

        $this->assertEquals(["word" => "Hello"], $config->resolveByName("custom-element"));
        $this->assertEquals(["key" => "value"], $config->resolveByName("complex-config"));
    }
}