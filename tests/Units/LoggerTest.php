<?php

namespace YonisSavary\Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Core\Logger;

class LoggerTest extends TestCase
{
    protected function genericLoggerTest(
        callable $callback,
        Logger $logger=null,
    ) {
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
}