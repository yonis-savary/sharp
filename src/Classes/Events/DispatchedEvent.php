<?php

namespace YonisSavary\Sharp\Classes\Events;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;

/**
 * This event is triggered when any other event is triggered
 */
class DispatchedEvent extends AbstractEvent
{
    public function __construct(
        public AbstractEvent $dispatched,
        public array $results=[]
    ){}
}