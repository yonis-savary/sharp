<?php

namespace YonisSavary\Sharp\Classes\Utils;

use YonisSavary\Sharp\Classes\Core\AbstractMap;
use YonisSavary\Sharp\Classes\Env\Storage;

final class AppMapInstance extends AbstractMap {

    private Storage $dataStorage;
    private string $hashName;

    public function __construct(Storage $dataStorage, string $hashName, $data)
    {
        $this->dataStorage = $dataStorage;
        $this->hashName = $hashName;
        $this->storage = $data;
    }

    public function __destruct()
    {
        $this->dataStorage->write($this->hashName, serialize($this->storage));
    }
}