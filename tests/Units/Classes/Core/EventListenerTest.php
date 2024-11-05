<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Core;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Events\CustomEvent;

class EventListenerTest extends TestCase
{
    public function test_dispatch()
    {
        $myVar = 0;

        $handlerA = new EventListener();
        $handlerA->on('change', function() use (&$myVar) { $myVar = 1; });

        $handlerB = new EventListener();
        $handlerB->on('change', function() use (&$myVar) { $myVar = 2; });

        $handlerA->dispatch(new CustomEvent('change'));
        $this->assertEquals(1, $myVar);

        $handlerB->dispatch(new CustomEvent('change'));
        $this->assertEquals(2, $myVar);

        $handlerA->dispatch(new CustomEvent('change'));
        $this->assertEquals(1, $myVar);

        $handlerC = new EventListener();
        $handlerC->on('change', function(CustomEvent $value) use (&$myVar) {
            $myVar = $value->extra['value'];
        });

        for($i=0; $i<5; $i++)
        {
            $handlerC->dispatch(new CustomEvent('change', ['value' => $i]));
            $this->assertEquals($i, $myVar);
        }
    }

    public function test_once()
    {
        $listener = new EventListener();

        $myVar = 0;

        $listener->on('increment', function() use (&$myVar){ $myVar++; });
        $listener->on('increment-once', function() use (&$myVar){ $myVar++; }, true);

        $this->assertEquals(0, $myVar);
        $listener->dispatch(new CustomEvent('increment'));
        $this->assertEquals(1, $myVar);
        $listener->dispatch(new CustomEvent('increment'));
        $this->assertEquals(2, $myVar);


        $listener->dispatch(new CustomEvent('increment-once'));
        $this->assertEquals(3, $myVar);
        $listener->dispatch(new CustomEvent('increment-once'));
        $this->assertEquals(3, $myVar);
    }

    public function test_delete()
    {
        $listener = new EventListener();

        $myVar = 0;

        $subscriptionId = $listener->on('increment', function() use (&$myVar){ $myVar++; });

        $this->assertEquals(0, $myVar);
        $listener->dispatch(new CustomEvent('increment'));
        $this->assertEquals(1, $myVar);

        $listener->removeSubscription($subscriptionId);
        $listener->dispatch(new CustomEvent('increment'));
        $this->assertEquals(1, $myVar);
    }

    public function test_removeSubscription()
    {
        $dispatcher = new EventListener();

        $i = 0;
        $id = $dispatcher->on("increment", function() use (&$i) { $i++; });

        $dispatcher->dispatch(new CustomEvent("increment"));
        $dispatcher->dispatch(new CustomEvent("increment"));
        $this->assertEquals(2, $i);

        $dispatcher->removeSubscription($id);
        $dispatcher->dispatch(new CustomEvent("increment"));
        $this->assertEquals(2, $i);
    }

    public function test_removeAllForEvent()
    {
        $dispatcher = new EventListener;

        $i = 0;
        $dispatcher->on("increment", function() use (&$i) { $i++; });

        $dispatcher->dispatch(new CustomEvent("increment"));
        $this->assertEquals(1, $i);

        $dispatcher->removeAllForEvent("increment");
        $dispatcher->dispatch(new CustomEvent("increment"));
        $this->assertEquals(1, $i);
    }

    public function test_getAllForEvent()
    {
        $dispatcher = new EventListener;

        $dispatcher->on("increment", fn() => "one", false);
        $dispatcher->on("increment", fn() => "two", true);

        $events = $dispatcher->getAllForEvent("increment");

        $this->assertCount(2, $events);

        $this->assertEquals("increment", $events[0][1]);
        $this->assertEquals("increment", $events[1][1]);
    }
}