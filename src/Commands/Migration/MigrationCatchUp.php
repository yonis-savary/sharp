<?php

namespace YonisSavary\Sharp\Commands\Migration;

use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Data\MigrationManager;

class MigrationCatchUp extends AbstractCommand
{
    public function getHelp(): string
    {
        return "Make your database catch up some migration without executing them (migrations will be marked as applied)";
    }

    public function __invoke(Args $args)
    {
        $manager = MigrationManager::getInstance();

        if (! $name = $args->values()[0] ?? false)
            $name = Terminal::prompt("migration name ? ");

        if (!$name)
            return $this->log("Please enter a valid migration name.");

        if (!$manager->migrationExists($name))
            return $this->log("No [$name] migration found !");

        $this->log("Catching up to migration [$name]");
        $files = $manager->catchUpTo($name);

        $this->log("Marked these files as applied to your database");
        foreach ($files as $file)
            $this->log(" - $file");
    }
}