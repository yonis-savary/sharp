<?php

namespace YonisSavary\Sharp\Classes\CLI;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Events\CalledCommand;
use YonisSavary\Sharp\Commands\Help;
use YonisSavary\Sharp\Core\Autoloader;

class Console extends CLIUtils
{
    use Component;

    /**
     * @return array<AbstractCommand>
     */
    public static function listCommands(): array
    {
        return ObjectArray::fromArray(Autoloader::classesThatExtends(AbstractCommand::class))
        ->map(fn($x) => new $x())
        ->filter(fn(AbstractCommand $x) => $x->getOrigin() != 'tests')
        ->collect();
    }

    /**
     * @return array<AbstractCommand>
     */
    public function findCommands(string $subject): array
    {
        return ObjectArray::fromArray(self::listCommands())
        ->filter(fn (AbstractCommand $command) => in_array($subject, [$command->getName(), $command->getIdentifier()]))
        ->collect();
    }

    public function printCommandList(): void
    {
        Help::call('');
    }

    /**
     * Handle PHP's $argv variable by trying to find a command that match it,
     * and then execute it by giving it arguments
     * @param array $argv Raw PHP $argv variable
     */
    public function handleArgv(array $argv): int
    {
        array_shift($argv); // Ignore script name !

        if (!count($argv))
        {
            $this->log('A command name is needed !');
            $this->printCommandList();
            return 0;
        }

        $commandName = array_shift($argv);
        $commands = $this->findCommands($commandName);

        if (!count($commands))
        {
            $this->log("No command with [$commandName] identifier found !");
            $this->printCommandList();
            return 1;
        }

        if (count($commands) > 1)
        {
            $this->log("Multiple commands for identifier [$commandName] found !");
            foreach ($commands as $command)
                $this->log(' - ' . $command->getIdentifier());
            return 2;
        }

        $args = Args::fromArray($argv);

        $command = $commands[0];

        if (!$args->isPresent(null, "command-output-only"))
            $this->log(
                sprintf("%s[ %s ]%s\n", str_repeat('-', 5), $command->getIdentifier() , str_repeat('-', 25))
            );
        $return = intval($command->execute($args));

        EventListener::getInstance()->dispatch(new CalledCommand($command, $return));

        if (!$args->isPresent(null, "command-output-only"))
            $this->log("");
        return $return;
    }
}