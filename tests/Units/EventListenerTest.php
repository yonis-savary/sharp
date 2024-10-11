<?php

namespace YonisSavary\Sharp\Tests\Units;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Events\CustomEvent;
use YonisSavary\Sharp\Classes\Core\EventListener;

class EventListenerTest extends TestCase
{
    public function test_events()
    {
        $myVar = 0;

        $handlerA = new EventListener();
        $handlerA->on("change", function() use (&$myVar) { $myVar = 1; });

        $handlerB = new EventListener();
        $handlerB->on("change", function() use (&$myVar) { $myVar = 2; });

        $handlerA->dispatch(new CustomEvent("change"));
        $this->assertEquals(1, $myVar);

        $handlerB->dispatch(new CustomEvent("change"));
        $this->assertEquals(2, $myVar);

        $handlerA->dispatch(new CustomEvent("change"));
        $this->assertEquals(1, $myVar);

        $handlerC = new EventListener();
        $handlerC->on("change", function(CustomEvent $value) use (&$myVar) {
            $myVar = $value->extra["value"];
        });

        for($i=0; $i<5; $i++)
        {
            $handlerC->dispatch(new CustomEvent("change", ["value" => $i]));
            $this->assertEquals($i, $myVar);
        }
    }

    public function test_once()
    {
        $listener = new EventListener();

        $myVar = 0;

        $listener->on("increment", function() use (&$myVar){ $myVar++; });
        $listener->on("increment-once", function() use (&$myVar){ $myVar++; }, true);

        $this->assertEquals(0, $myVar);
        $listener->dispatch(new CustomEvent("increment"));
        $this->assertEquals(1, $myVar);
        $listener->dispatch(new CustomEvent("increment"));
        $this->assertEquals(2, $myVar);


        $listener->dispatch(new CustomEvent("increment-once"));
        $this->assertEquals(3, $myVar);
        $listener->dispatch(new CustomEvent("increment-once"));
        $this->assertEquals(3, $myVar);
    }

    public function test_delete()
    {
        $listener = new EventListener();

        $myVar = 0;

        $subscriptionId = $listener->on("increment", function() use (&$myVar){ $myVar++; });

        $this->assertEquals(0, $myVar);
        $listener->dispatch(new CustomEvent("increment"));
        $this->assertEquals(1, $myVar);

        $listener->removeSubscription($subscriptionId);
        $listener->dispatch(new CustomEvent("increment"));
        $this->assertEquals(1, $myVar);
    }
}