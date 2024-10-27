<?php

namespace YonisSavary\Sharp\Commands\Migration;

use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\Data\MigrationManager;

class MigrationList extends AbstractCommand
{
    public function __invoke(Args $args)
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
    }
}