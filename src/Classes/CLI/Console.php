<?php

namespace YonisSavary\Sharp\Classes\CLI;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Events\CalledCommand;
use YonisSavary\Sharp\Commands\Help;
use YonisSavary\Sharp\Core\Autoloader;

class Console
{
    use Component;

    /**
     * @return array<Command>
     */
    public static function listCommands(): array
    {
        return ObjectArray::fromArray(Autoloader::classesThatExtends(Command::class))
        ->map(fn($x) => new $x())
        ->filter(fn(Command $x) => $x->getOrigin() != "tests")
        ->collect();
    }

    /**
     * @return array<Command>
     */
    public function findCommands(string $identifier): array
    {
        return ObjectArray::fromArray(self::listCommands())
        ->filter(fn (Command $command) => in_array($identifier, [$command->getName(), $command->getIdentifier()]))
        ->collect();
    }

    public function printCommandList(): void
    {
        $help = new Help();
        $nullArgs = new Args();

        $help($nullArgs);
    }

    /**
     * Handle PHP's $argv variable by trying to find a command that match it,
     * and then execute it by giving it arguments
     * @param array $argv Raw PHP $argv variable
     */
    public function handleArgv(array $argv): void
    {
        array_shift($argv); // Ignore script name !

        if (!count($argv))
        {
            print("A command name is needed !\n");
            $this->printCommandList();
            return;
        }

        $commandName = array_shift($argv);
        $commands = $this->findCommands($commandName);

        if (!count($commands))
        {
            print("No command with [$commandName] identifier found !\n");
            $this->printCommandList();
            return;
        }

        if (count($commands) > 1)
        {
            echo "Multiple commands for identifier [$commandName] found !\n";
            foreach ($commands as $command)
                echo " - " . $command->getIdentifier() . "\n";
            return;
        }

        $command = $commands[0];

        printf("%s[ %s ]%s\n", str_repeat("-", 5), $command->getIdentifier() , str_repeat("-", 25));
        $return = $command(Args::fromArray($argv));

        EventListener::getInstance()->dispatch(new CalledCommand($command, $return));
    }
}