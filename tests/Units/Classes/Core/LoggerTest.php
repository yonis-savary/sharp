<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Core;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Env\Storage;

class LoggerTest extends TestCase
{
    public function genericLoggerTest(callable $callback, Logger $logger=null)
    {
        $logger ??= new Logger(uniqid('log').'.csv');

        $original = md5_file($logger->getPath());
        $callback($logger);

        $this->assertNotEquals(
            md5_file($logger->getPath()),
            $original
        );
    }

    public function test_construct()
    {
        $logger = new Logger(uniqid('log'));
        $this->assertInstanceOf(Logger::class, $logger);

        $logger = new Logger(uniqid('dir') . '/' . uniqid('log'));
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function test_log()
    {
        $this->genericLoggerTest(
        function(Logger $logger){ $logger->log('my-level', 'hello'); });
    }

    public function test_debug()
    {
        $this->genericLoggerTest(
        function(Logger $logger){ $logger->debug('hello'); });
    }

    public function test_info()
    {
        $this->genericLoggerTest(
        function(Logger $logger){ $logger->info('hello'); });
    }

    public function test_notice()
    {
        $this->genericLoggerTest(
        function(Logger $logger){ $logger->notice('hello'); });
    }

    public function test_warning()
    {
        $this->genericLoggerTest(
        function(Logger $logger){ $logger->warning('hello'); });
    }

    public function test_error()
    {
        $this->genericLoggerTest(
        function(Logger $logger){ $logger->error('hello'); });
    }

    public function test_critical()
    {
        $this->genericLoggerTest(
        function(Logger $logger){ $logger->critical('hello'); });
    }

    public function test_alert()
    {
        $this->genericLoggerTest(
        function(Logger $logger){ $logger->alert('hello'); });
    }

    public function test_emergency()
    {
        $this->genericLoggerTest(
        function(Logger $logger){ $logger->emergency('hello'); });
    }

    public function test_fromStream()
    {
        $logger = Logger::fromStream(fopen('php://output', 'w'));

        ob_start();
        $logger->info('Hello');
        $output = ob_get_clean();

        $this->assertStringContainsString('Hello', $output);
    }

    public function test_replaceStream()
    {
        $logger = Logger::fromStream(fopen('php://output', 'w'), true);

        ob_start();
        $logger->info('Hello');
        $output = ob_get_clean();
        $this->assertStringContainsString('Hello', $output);

        $secondStreamFile = Storage::getInstance()->path(uniqid("temp-log-").".csv");
        $secondStream = fopen($secondStreamFile, "w");
        $logger->replaceStream($secondStream, true);

        ob_start();
        $logger->info('Hello');
        $output = ob_get_clean();
        $this->assertStringNotContainsString('Hello', $output);
        $this->assertStringContainsString('Hello', file_get_contents($secondStreamFile));
    }

    public function test_getPath()
    {
        $identifier = uniqid("temp-log-").".csv";
        $storage = Storage::getInstance()->getSubStorage("Logs");

        $fullPath = $storage->path($identifier);

        $logger = new Logger($identifier, $storage);
        $this->assertEquals(
            $fullPath,
            $logger->getPath()
        );
    }


}