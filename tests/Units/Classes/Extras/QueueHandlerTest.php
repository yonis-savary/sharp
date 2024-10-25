<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Extras;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Extras\QueueHandler;

class QueueHandlerTest extends TestCase
{
    public function test_processQueueItem()
    {
        $handler = new class {
            use QueueHandler;

            static int $acc = 0;
            static ?int $lastProcessed = null;

            public static function addNumber(int $number, bool $process=true)
            {
                self::pushQueueItem(['number' => $number, "process" => $process]);
            }

            protected static function processQueueItem(array $data): bool
            {
                if (!$data["process"])
                    return false;

                $n = $data['number'];
                self::$lastProcessed = $n;
                self::$acc += $n;
                return true;
            }
        };


        for ($i=1; $i<=40; $i++)
            $handler::addNumber($i, $i<=30);


        $sumOfN = fn($n) => ($n**2 + $n) / 2;

        $handler::processQueue();
        $this->assertEquals(10, $handler::$lastProcessed);
        $this->assertEquals($sumOfN(10) , $handler::$acc);

        $handler::processQueue();
        $this->assertEquals(20, $handler::$lastProcessed);
        $this->assertEquals($sumOfN(20) , $handler::$acc);

        $handler::processQueue();
        $this->assertEquals(30, $handler::$lastProcessed);
        $this->assertEquals($sumOfN(30) , $handler::$acc);

        $handler::processQueue();
        $this->assertEquals(30, $handler::$lastProcessed);
        $this->assertEquals($sumOfN(30) , $handler::$acc);

    }
}