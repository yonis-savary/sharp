<?php

namespace YonisSavary\Sharp\Classes\Core;

use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Events\DispatchedEvent;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

class EventListener
{
    use Component;

    protected array $subscriptions = [];
    protected array $subscriptionsCache = [];

    /**
     * Attach callback(s) to an event
     * When the given event is triggered, all given callbacks are called
     *
     * @param string $event Event name, usually a class name from `EventClass::class`
     * @param callable $callback Callback to call on dispatch
     * @param bool $once If true, the callback shall be called once per request
     * @return int Subscription id
     */
    public function on(string $event, callable $callback, bool $once=false): int
    {
        $newIndex = count($this->subscriptions);
        array_push($this->subscriptions, [$newIndex, $event, $callback, $once]);

        return $newIndex;
    }

    public function removeAllForEvent(string $event)
    {
        $this->subscriptions = ObjectArray::fromArray($this->subscriptions)
        ->filter(fn($subscription) => $subscription[1] !== $event)
        ->collect();
    }

    public function getAllForEvent(string $event)
    {
        return ObjectArray::fromArray($this->subscriptions)
        ->filter(fn($subscription) => $subscription[1] === $event)
        ->collect();
    }

    public function removeSubscription(int $subscriptionId): bool
    {
        $subscription = $this->subscriptions[$subscriptionId] ?? null;

        if (!$subscription)
            return false;

        $this->subscriptions[$subscriptionId] = null;
        return true;
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

        $results = ObjectArray::fromArray($this->subscriptions)
        ->filter(fn($x) => $x && $x[1] === $eventName)
        ->map(function($subscription) use ($event) {
            list($id, $_, $handler, $once) = $subscription;

            if ($once)
                $this->removeSubscription($id);

            return $handler($event);
        })
        ->collect();

        if ($eventName !== DispatchedEvent::class)
            $this->dispatch(new DispatchedEvent($event, $results));
    }
}