<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Test\SharpServer;

class Serve extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Start built-in PHP server in Public, default port is 8000 (ex: php do serve 5000)';
    }

    public function __invoke(Args $args)
    {
        $port = (int) ($args->get("p", "port") ?? 8000);
        $host = $args->get("h", "hostname") ?? "localhost";

        $server = new SharpServer($port, hostname: $host);
        $this->log(
            '',
            "Serving on port $port (".($server->getURL()).')...',
            ''
        );

        $defaultCallback = fn($x) => $this->withDefaultColor($x, false);
        $callback = $defaultCallback;
        while ($server->isRunning())
        {
            $output = trim($server->getIncrementalOutput() . $server->getIncrementalErrorOutput());

            if (trim($output))
            {
                ObjectArray::fromExplode("\n", $output)
                ->forEach(function($line) use (&$callback, $defaultCallback) {

                    $matches = [];
                    if (preg_match("/\[(\d+)\]/", $line, $matches))
                    {
                        $responseType = (int) ($matches[1] / 100);

                        $callback = match($responseType) {
                            2 => fn($x) => $this->withGreenColor($x, false),
                            3 => fn($x) => $this->withBlueColor($x, false),
                            4 => fn($x) => $this->withYellowColor($x, false),
                            5 => fn($x) => $this->withRedColor($x, false),
                            default => fn($x) => $this->withDefaultColor($x, false),
                        };
                    }
                    else if (str_ends_with($line, "Accepted"))
                    {
                        $callback = $defaultCallback;
                    }

                    $this->log($callback($line));
                });
            }

            usleep(1000* 100);
        }

    }
}