<?php

namespace YonisSavary\Sharp\Classes\Core;

use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Events\DispatchedEvent;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

class EventListener
{
    use Component;

    protected array $handlers = [];

    /**
     * Attach callback(s) to an event
     * When the given event is triggered, all given callbacks are called
     */
    public function on(string $event, callable ...$callbacks): void
    {
        $this->handlers[$event] ??= [];
        array_push($this->handlers[$event], ...$callbacks);
    }

    /**
     * Trigger an event and call every attached callbacks (if any)
     *
     * @param AbstractEvent $event Event object to trigger
     * @param mixed ...$args Parameters to give to the event's callbacks
     */
    public function dispatch(AbstractEvent $event): void
    {
        $eventName = $event->getName();

        $results = ObjectArray::fromArray($this->handlers[$eventName] ?? [])
        ->map(fn($handler) => $handler($event))
        ->collect();

        if ($eventName !== DispatchedEvent::class)
            $this->dispatch(new DispatchedEvent($event, $results));
    }
}