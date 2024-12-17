<?php

namespace YonisSavary\Sharp\Classes\Utils;

use YonisSavary\Sharp\Classes\Core\GenericMap;
use YonisSavary\Sharp\Classes\Env\Storage;

final class AppMapInstance extends GenericMap {

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