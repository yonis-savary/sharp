<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Data;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\ModelQueryIterator;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestTvShow;
use YonisSavary\Sharp\Tests\Units\TestClassFactory;

class ModelQueryIteratorTest extends TestCase
{
    protected function setUp(): void
    {
        $db = TestClassFactory::createDatabase();
        Database::setInstance($db);
    }

    public function test_forEach()
    {
        TestTvShow::select()->forEach(function($show){
            $this->assertInstanceOf(TestTvShow::class, $show);
        });

        ModelQueryIterator::forEach(TestTvShow::select(), function($show){
            $this->assertInstanceOf(TestTvShow::class, $show);
        });
    }

    public function test_getCount()
    {
        $iterator = new ModelQueryIterator(TestTvShow::select());
        $this->assertEquals(5, $iterator->getCount());
    }
}