<?php

namespace YonisSavary\Sharp\Classes\Events;

use PDO;
use YonisSavary\Sharp\Classes\Core\AbstractEvent;

/**
 * This event is triggered when a connection to a database is made
 */
class ConnectedDatabase extends AbstractEvent
{
    public function __construct(
        public PDO $connection,
        public ?string $driver=null,
        public ?string $database=null,
        public ?string $host=null,
        public ?string $port=null,
        public ?string $user=null,
    ){}
}