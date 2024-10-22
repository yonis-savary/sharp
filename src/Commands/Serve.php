<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;

class Serve extends AbstractCommand
{
    public function getHelp(): string
    {
        return "Start built-in PHP server in Public, default port is 8000 (ex: php do serve 5000)";
    }

    public function __invoke(Args $args)
    {
        $port = intval($args->values()[0] ?? 8000);

        $this->log(
            "",
            "Serving on port $port (http://localhost:$port)...",
            ""
        );

        chdir("Public");
        $proc = popen("php -S localhost:$port", "r");

        while (!feof($proc))
            $this->log(fread($proc, 1024));
    }
}