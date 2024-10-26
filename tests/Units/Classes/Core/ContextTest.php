<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Core;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Core\Context;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

/**
 * Context is a static storage for every type of variable
 * This class can store one instance of every variable type
 *
 * It can be useful to retrieve the current request for example
 */
class ContextTest extends TestCase
{

    public function test_setAndGet()
    {
        $this->assertNull(Context::get(ObjectArray::class));

        $array = ObjectArray::fromArray([1,2,3]);

        Context::set($array);

        /** @var ObjectArray */
        $stillArray = Context::get(ObjectArray::class);
        $this->assertEquals([1,2,3], $stillArray->collect());


        $newArray = ObjectArray::fromArray([4,5,6]);
        Context::set($newArray);

        $stillNewArray = Context::get(ObjectArray::class);
        $this->assertEquals([4,5,6], $stillNewArray->collect());

        Context::forget(ObjectArray::class);
        $this->assertNull(Context::get(ObjectArray::class));
    }

    public function test_instanceGetter()
    {
        $component = ComponentTest::getDummyComponent();
        $component::removeInstance();
        Context::forget($component::class);

        /** @var TestComponent */
        $component = Context::get($component::class);
        $this->assertEquals(0, $component->getNumber());
    }
}