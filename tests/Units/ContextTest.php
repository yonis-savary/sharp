<?php

namespace YonisSavary\Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Core\Context;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Tests\TestApp\Components\TestComponent;

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
        TestComponent::removeInstance();
        Context::forget(TestComponent::class);

        /** @var TestComponent */
        $component = Context::get(TestComponent::class);
        $this->assertEquals('default', $component->getName());
    }
}