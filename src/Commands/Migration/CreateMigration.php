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

    public function execute(Args $args): int
    {
        $manager = MigrationManager::getInstance();

        if (! $name = $args->values()[0] ?? false)
            $name = Terminal::prompt("migration name ? ");

        if (!$name)
        {
            $this->log("Please enter a valid migration name.");
            return 1;
        }

        $path = $manager->createMigration($name);
        $this->log("Made migration at [$path]");

        return 0;
    }
}