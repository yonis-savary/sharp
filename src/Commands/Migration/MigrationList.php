<?php

namespace YonisSavary\Sharp\Commands\Migration;

use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\Data\MigrationManager;

class MigrationList extends AbstractCommand
{
    public function getHelp(): string
    {
        return "List all available/done migrations";
    }

    public function execute(Args $args): int
    {
        $manager = MigrationManager::getInstance();

        $this->log("Available/Done migrations on your database");
        foreach ($manager->listAllMigrations() as $name)
        {
            $string = " - $name";

            $string = $manager->migrationWasMade($name) ?
                $this->withGreenColor($string):
                $this->withYellowColor($string);

            $this->log($string);
        }

        return 0;
    }
}