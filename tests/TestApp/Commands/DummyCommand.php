<?php

namespace YonisSavary\Sharp\Tests\TestApp\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;

class DummyCommand extends Command
{
    public function __invoke(Args $args)
    {
        $this->log("Hello");
    }

    public function getHelp(): string
    {
        return "Help";
    }
}