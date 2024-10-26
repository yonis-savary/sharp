<?php

namespace YonisSavary\Sharp\Classes\Events\AutobahnEvents;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Data\ModelQuery;

/**
 * This event is triggered after inserting multiple rows with Autobahn
 */
class AutobahnMultipleCreateAfter extends AbstractEvent
{
    public function __construct(
        public string $model,
        public ModelQuery &$query,
        public ?array $insertedIdList
    ){}
}