<?php

namespace YonisSavary\Sharp\Classes\Data\ModelGenerator;

use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

abstract class GeneratorDriver
{
    protected Database $connection;

    public function __construct(Database $connection)
    {
        $this->connection = $connection;
    }

    /**
     * snake_case to PascalCase converter
     */
    protected function sqlToPHPName(string $name): string
    {
        return ObjectArray::fromExplode('_', $name)
        ->filter()
        ->map(ucfirst(...))
        ->join();
    }

    public function generateAll(string $targetApplication, string $modelNamespace=null): void
    {
        foreach ($this->listTables() as $table)
            $this->generate($table, $targetApplication, $modelNamespace);
    }

    /**
     * @var array<string> Return an array with tables names
     */
    abstract public function listTables(): array;

    abstract public function generate(string $table, string $targetApplication, string $modelNamespace=null);
}