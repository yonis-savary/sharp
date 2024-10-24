[< Back to summary](../README.md)

# 🪝 Events

Sharp got the [`EventListener`](../../src/Classes/Core/EventListener.php) class, which allows you to add event listeners/hooks

```php
$events = EventListener::getInstance();

$events->on('log-this', function(CustomEvent $event){
    Logger::getInstance()->debug($event->extra['text']);
});

$events->dispatch(new CustomEvent('log-this', ['text' => 'Hello world']));
```

## Specific Event Classes

You can make your own event class by making a class that extends from [`AbstractEvent`](../../src/Classes/Core/AbstractEvent.php)

`MyApp\Classes\Events\ShippedOrder.php`
```php
class ShippedOrder
{
    public function __construct(
        public Order $order
    ){}
}
```

Then you can trigger it by giving `dispatch` an instance of the class

```php
$eventListener->dispatch(new ShippedOrder($order));
```

And listen for it with its classname

```php
$eventListener->on(ShippedOrder::class, function(ShippedOrder $event){
    debug('Sent order N°' . $event->order->id);
});
```

## Framework Base Events

The framework has some base `AbstractEvent` object that are automatically dispatched on certain condition

Base/General events:
- [`BeforeViewRender`](../../src/Classes/Events/BeforeViewRender.php)
- [`CalledCommand`](../../src/Classes/Events/CalledCommand.php)
- [`ConnectedDatabase`](../../src/Classes/Events/ConnectedDatabase.php)
- [`DispatchedEvent`](../../src/Classes/Events/DispatchedEvent.php)
- [`FailedAutoload`](../../src/Classes/Events/FailedAutoload.php)
- [`LoadedFramework`](../../src/Classes/Events/LoadedFramework.php)
- [`RouteNotFound`](../../src/Classes/Events/RouteNotFound.php)
- [`RoutedRequest`](../../src/Classes/Events/RoutedRequest.php)
- [`UncaughtException`](../../src/Classes/Events/UncaughtException.php)

Autobahn's Events:
- [`AutobahnCreateAfter`](../../src/Classes/Events/AutobahnEvents/AutobahnCreateAfter.php)
- [`AutobahnCreateBefore`](../../src/Classes/Events/AutobahnEvents/AutobahnCreateBefore.php)
- [`AutobahnDeleteAfter`](../../src/Classes/Events/AutobahnEvents/AutobahnDeleteAfter.php)
- [`AutobahnDeleteBefore`](../../src/Classes/Events/AutobahnEvents/AutobahnDeleteBefore.php)
- [`AutobahnMultipleCreateAfter`](../../src/Classes/Events/AutobahnEvents/AutobahnMultipleCreateAfter.php)
- [`AutobahnMultipleCreateBefore`](../../src/Classes/Events/AutobahnEvents/AutobahnMultipleCreateBefore.php)
- [`AutobahnReadAfter`](../../src/Classes/Events/AutobahnEvents/AutobahnReadAfter.php)
- [`AutobahnReadBefore`](../../src/Classes/Events/AutobahnEvents/AutobahnReadBefore.php)
- [`AutobahnUpdateAfter`](../../src/Classes/Events/AutobahnEvents/AutobahnUpdateAfter.php)
- [`AutobahnUpdateBefore`](../../src/Classes/Events/AutobahnEvents/AutobahnUpdateBefore.php)

[< Back to summary](../README.md)