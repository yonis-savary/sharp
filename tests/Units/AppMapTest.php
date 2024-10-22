<?php

namespace YonisSavary\Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Tests\TestApp\Classes\AppMapA;
use YonisSavary\Sharp\Tests\TestApp\Classes\AppMapB;

class AppMapTest extends TestCase
{
    public function test_collision()
    {
        $a = AppMapA::get();
        $b = AppMapB::get();

        $a->set("key", "abc");
        $b->set("key", "123");

        $this->assertEquals("abc", $a->get("key"));
        $this->assertEquals("123", $b->get("key"));
    }

    public function test_reference()
    {
        $first = AppMapA::get();
        $second = AppMapA::get();


        $first->set("refTest", "Hello!");

        $this->assertEquals("Hello!", $second->get("refTest"));
    }
}