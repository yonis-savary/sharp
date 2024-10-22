<?php

namespace YonisSavary\Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Tests\TestApp\Classes\AppStorageA;
use YonisSavary\Sharp\Tests\TestApp\Classes\AppStorageB;

class AppStorageTest extends TestCase
{
    public function test_collision()
    {
        $a = AppStorageA::get();
        $b = AppStorageB::get();

        $a->write("text.txt", "Hello");
        $b->write("text.txt", "Goodbye");

        $this->assertEquals("Hello", $a->read("text.txt"));
        $this->assertEquals("Goodbye", $b->read("text.txt"));
    }
}