<?php

namespace YonisSavary\Sharp\Classes\Events;

use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Core\AbstractEvent;

/**
 * This event is triggered when a command is called through `Console`
 */
class CalledCommand extends AbstractEvent
{
    public function __construct(
        public AbstractCommand $command,
        public mixed $returnedValue = null
    ) {}
}