<?php

namespace YonisSavary\Sharp\Classes\Events\AutobahnEvents;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;

/**
 * This event is triggered before creating a row with Autobahn
 */
class AutobahnCreateBefore extends AbstractEvent
{
    public function __construct(
        public string $model,
        public array $fields,
        public array &$values
    ){}
}