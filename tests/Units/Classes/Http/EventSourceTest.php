<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Http;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Http\Configuration\EventSourceConfiguration;
use YonisSavary\Sharp\Classes\Http\EventSource;

class EventSourceTest extends TestCase
{
    public static function getDummyEventSource(EventSourceConfiguration $configuration=null): EventSource
    {
        $configuration ??= new EventSourceConfiguration(dieOnEnd: false);
        $eventSource = new EventSource($configuration);
        return $eventSource;
    }

    public function test_start()
    {
        $eventSource = self::getDummyEventSource();

        $this->assertFalse($eventSource->isStarted());
        ob_start();
        $eventSource->start("starting", false);
        $this->assertEquals("event: starting\ndata: null\n\n", ob_get_clean());
        $this->assertTrue($eventSource->isStarted());
    }

    public function test_send()
    {
        $eventSource = self::getDummyEventSource(new EventSourceConfiguration(null, null, false));
        $eventSource->start(null, sendHeaders: false);

        ob_start();
        $eventSource->send("my-event");
        $this->assertEquals("event: my-event\ndata: null\n\n", ob_get_clean());

        ob_start();
        $eventSource->send("my-event", ["A" => 5]);
        $this->assertEquals("event: my-event\ndata: {\"A\":5}\n\n", ob_get_clean());

        ob_start();
        $eventSource->send("my-event", ["A" => 5], "my-id");
        $this->assertEquals("event: my-event\ndata: {\"A\":5}\nid: my-id\n\n", ob_get_clean());

        ob_start();
        $eventSource->send("my-event", ["A" => 5], "my-id", 5);
        $this->assertEquals("event: my-event\ndata: {\"A\":5}\nid: my-id\nretry: 5\n\n", ob_get_clean());
    }

    public function test_data()
    {
        $eventSource = self::getDummyEventSource(new EventSourceConfiguration(null, null, false));
        $eventSource->start(sendHeaders: false);

        ob_start();
        $eventSource->data(["A" => 5]);
        $this->assertEquals("data: {\"A\":5}\n\n", ob_get_clean());
    }

    public function test_end()
    {
        $eventSource = self::getDummyEventSource(new EventSourceConfiguration(null, null, false));
        $eventSource->start(sendHeaders: false);

        $this->assertTrue($eventSource->isStarted());

        ob_start();
        $eventSource->end("ending");
        $this->assertEquals("event: ending\ndata: null\n\n", ob_get_clean());

        $this->assertFalse($eventSource->isStarted());
    }
}