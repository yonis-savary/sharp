<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Env;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use YonisSavary\Sharp\Classes\Env\Configuration\JSONConfiguration;
use YonisSavary\Sharp\Classes\Env\Storage;

class JSONConfigurationTest extends TestCase
{
    // Most of Configuration feature are tested by [./GenericMapTest.php]

    public function test___construct()
    {
        $storage = Storage::getInstance();

        $storage->write(
            'config-test-construct.json',
            json_encode(['A' => 5])
        );

        $config = new JSONConfiguration($storage->path('config-test-construct.json'));
        $this->assertEquals(5, $config->get('A'));
    }

    public function test_save()
    {
        $storage = Storage::getInstance();

        $file = $storage->path('config-test.json');

        $unrelated = new JSONConfiguration();
        $unrelated->set('key', 'A');
        $unrelated->save($file);

        $fromFile = new JSONConfiguration($file);
        $this->assertEquals('A', $fromFile->get('key'));
    }

    public function test_fromArray()
    {
        $config = JSONConfiguration::fromArray([
            'A' => 1,
            'B' => 2,
            'C' => 3,
        ]);

        $this->assertEquals(1, $config->get('A'));
        $this->assertEquals(2, $config->get('B'));
        $this->assertEquals(3, $config->get('C'));
        $this->assertNull($config->get('D'));
    }

    public function test_mergeWithFile()
    {
        $storage = Storage::getInstance();
        $storage->write('config-a.json', '{"a": "first config"}');
        $storage->write('config-b.json', '{"b": "second config"}');

        $mergeConfig = new JSONConfiguration($storage->path('config-a.json'));

        $this->assertEquals('first config', $mergeConfig->get('a'));

        $this->expectException(RuntimeException::class);
        $mergeConfig->mergeWithFile($storage->path('config-c.json'), true);

        $mergeConfig->mergeWithFile($storage->path('config-b.json'), true);

        $this->assertEquals('second config', $mergeConfig->get('b'));

        $this->expectException(RuntimeException::class);
        $mergeConfig->save();
    }

}