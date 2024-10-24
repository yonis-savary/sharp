<?php

namespace YonisSavary\Sharp\Tests\TestApp\Commands;

use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Args;

class DummyLoad extends AbstractCommand
{
    public function __invoke(Args $args)
    {
        $this->progressBar(range(0, 10), function($i){
            echo "LOAD $i";
            sleep(1);
        });
    }
}