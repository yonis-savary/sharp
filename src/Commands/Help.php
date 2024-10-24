<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Console;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

class Help extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Display a list of commands with a short description';
    }

    public function __invoke(Args $args)
    {
        /** @var array<Command> $commands */
        $commands = ObjectArray::fromArray(Console::listCommands())
        ->sortByKey(fn(AbstractCommand $command) => $command->getName())
        ->collect();

        $maxLength = [
            'name' => 0,
            'identifier' => 0
        ];

        foreach ($commands as $command)
        {
            $maxLength['name'] = max($maxLength['name'], strlen($command->getName()));
            $maxLength['identifier'] = max($maxLength['identifier'], strlen($command->getIdentifier()));
        }

        $this->log('Available commands with their identifier and purposes:');

        foreach ($commands as $command)
        {
            $this->log(
                sprintf(" - %s %s : %s",
                    str_pad($command->getName(), $maxLength['name']),
                    str_pad('('. $command->getIdentifier() .')', $maxLength['identifier']+2),
                    $command->getHelp()
                )
            );
        }
    }
}