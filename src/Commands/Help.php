<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\CLI\Console;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

class Help extends Command
{
    public function getHelp(): string
    {
        return "Display a list of commands with a short description";
    }

    public function __invoke(Args $args)
    {
        /** @var array<Command> $commands */
        $commands = ObjectArray::fromArray(Console::listCommands())
        ->sortByKey(fn(Command $command) => $command->getName())
        ->collect();

        $maxLength = [
            "name" => 0,
            "identifier" => 0
        ];

        foreach ($commands as $command)
        {
            $maxLength["name"] = max($maxLength["name"], strlen($command->getName()));
            $maxLength["identifier"] = max($maxLength["identifier"], strlen($command->getIdentifier()));
        }

        echo "Available commands with their identifier and purposes:\n";

        foreach ($commands as $command)
        {
            printf(" - %s %s : %s\n",
                str_pad($command->getName(), $maxLength["name"]),
                str_pad("(". $command->getIdentifier() .")", $maxLength["identifier"]+2),
                $command->getHelp()
            );
        }
    }
}