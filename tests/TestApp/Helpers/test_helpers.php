<?php

use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

function resetTestDatabase()
{
    $newDB = new Database('sqlite', null);

    $schema = file_get_contents( __DIR__.'/../schema.sql');
    $schema = ObjectArray::fromExplode(';', $schema)
    ->map(trim(...))
    ->filter()
    ->collect();

    foreach ($schema as $query)
        $newDB->query($query);

    Database::setInstance($newDB);
}