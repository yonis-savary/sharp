<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Core;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Core\Configurable;
use YonisSavary\Sharp\Classes\Env\Configuration;

class ConfigurableTest extends TestCase
{
    public static function getDummyConfigurable()
    {
        return new class {
            use Configurable;

            public function __construct()
            {

            }

            public static function getConfigurationKey(): string
            {
                return "dummy-configurable";
            }

            public static function getDefaultConfiguration(): array
            {
                return ['enabled' => true, 'cached' => false];
            }
        };
    }

    public function test_getDefaultConfiguration()
    {
        $dummyConfigurable = self::getDummyConfigurable();

        $this->assertEquals([
            'enabled' => true,
            'cached' => false
        ], $dummyConfigurable::getDefaultConfiguration());
    }

    public function test_getConfigurationKey()
    {
        $dummyConfigurable = self::getDummyConfigurable();
        $this->assertEquals(
            'dummy-configurable',
            $dummyConfigurable::getConfigurationKey()
        );
    }

    public function test_readConfiguration()
    {
        $dummyConfigurable = self::getDummyConfigurable();
        $configData = ['enabled' => false, 'cached' => false];

        $config = new Configuration();
        $config->set('dummy-configurable', $configData);

        $this->assertEquals(
            $configData,
            $dummyConfigurable::readConfiguration($config)
        );
    }

    public function test_configurationIsLoaded()
    {
        $dummyConfigurable = self::getDummyConfigurable();
        $instance = new $dummyConfigurable();

        $this->assertFalse($instance->configurationIsLoaded());

        $instance->loadConfiguration();
        $this->assertTrue($instance->configurationIsLoaded());
    }

    public function test_isEnabled()
    {
        $dummyConfigurable = self::getDummyConfigurable();
        $instance = new $dummyConfigurable();

        $instance->setConfiguration(['enabled' => false]);
        $this->assertFalse($instance->isEnabled());

        $instance->setConfiguration(['enabled' => true]);
        $this->assertTrue($instance->isEnabled());
    }

    public function test_isCached()
    {
        $dummyConfigurable = self::getDummyConfigurable();
        $instance = new $dummyConfigurable();

        $instance->setConfiguration(['cached' => false]);
        $this->assertFalse($instance->isCached());

        $instance->setConfiguration(['cached' => true]);
        $this->assertTrue($instance->isCached());
    }

    public function test_is()
    {
        $dummyConfigurable = self::getDummyConfigurable();
        $instance = new $dummyConfigurable();

        $instance->setConfiguration(['cached' => false]);
        $this->assertFalse($instance->is("cached"));

        $instance->setConfiguration(['cached' => true]);
        $this->assertTrue($instance->is("cached"));

    }


    public function test_getConfiguration()
    {
        $dummyConfigurable = self::getDummyConfigurable();
        $configData = ['enabled' => false, 'cached' => false];

        $config = new Configuration();
        $config->set('dummy-configurable', $configData);

        $instance = new $dummyConfigurable();
        $instance->loadConfiguration($config);

        $this->assertEquals(
            $configData,
            $instance->getConfiguration()
        );
    }

    public function test_setConfiguration()
    {
        $dummyConfigurable = self::getDummyConfigurable();
        $instance = new $dummyConfigurable();
        $instance->setConfiguration([]);

        $this->assertEquals(['enabled' => true, 'cached' => false], $instance->getConfiguration());

        $instance->setConfiguration(['enabled' => false]);
        $this->assertEquals(['enabled' => false, 'cached' => false], $instance->getConfiguration());

        $instance->setConfiguration(['cached' => true]);
        $this->assertEquals(['enabled' => false, 'cached' => true], $instance->getConfiguration());
    }
}