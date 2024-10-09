<?php

namespace YonisSavary\Sharp\Tests\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;

class DummyCommand extends Command
{
    public function __invoke(Args $args)
    {
        echo "Hello";
    }

    public function getHelp(): string
    {
        return "Help";
    }
}