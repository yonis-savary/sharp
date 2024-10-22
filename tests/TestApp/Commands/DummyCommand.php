<?php

namespace YonisSavary\Sharp\Tests\TestApp\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;

class DummyCommand extends AbstractCommand
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