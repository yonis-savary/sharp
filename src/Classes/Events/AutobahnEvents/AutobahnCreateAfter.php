<?php

namespace YonisSavary\Sharp\Classes\Events\AutobahnEvents;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Data\DatabaseQuery;

/**
 * This event is triggered after creating a row with Autobahn
 */
class AutobahnCreateAfter extends AbstractEvent
{
    public function __construct(
        public string $model,
        public array $fields,
        public array &$values,
        public ?int $insertedId
    ){}
}