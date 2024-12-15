<?php

namespace YonisSavary\Sharp\Classes\Http\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;

class EventSourceConfiguration
{
    use ConfigurationElement;

    /**
     * @param ?string $startEvent If set, will be sent to the client when the transmission begins
     * @param ?string $endEvent If set, will be sent to the client when the transmission ends
     * @param bool $dieOnEnd If `true` will `die()` when the transmission ends
     */
    public function __construct(
        public readonly ?string $startEvent = 'event-source-start',
        public readonly ?string $endEvent = 'event-source-end',
        public readonly bool $dieOnEnd = true
    ){}
}