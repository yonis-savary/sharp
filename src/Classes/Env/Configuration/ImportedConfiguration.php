<?php

namespace YonisSavary\Sharp\Classes\Env\Configuration;

use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Core\Utils;

class ImportedConfiguration
{
    public function __construct(public string $fileToImport)
    {
        if (!is_file($this->fileToImport))
            $this->fileToImport = Utils::relativePath($this->fileToImport);

        if (!is_file($this->fileToImport))
            Logger::getInstance()->error("Could not import ". $this->fileToImport ." configuration (not a file)");
    }
}