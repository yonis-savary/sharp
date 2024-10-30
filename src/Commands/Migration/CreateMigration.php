<?php

namespace YonisSavary\Sharp\Commands\Migration;

use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Data\MigrationManager;

class CreateMigration extends AbstractCommand
{
    public function getHelp(): string
    {
        return "Create a migration file and display the output path";
    }

    public function __invoke(Args $args)
    {
        $manager = MigrationManager::getInstance();

        if (! $name = $args->values()[0] ?? false)
            $name = Terminal::prompt("migration name ? ");

        if (!$name)
            return $this->log("Please enter a valid migration name.");

        $path = $manager->createMigration($name);
        $this->log("Made migration at [$path]");
    }
}