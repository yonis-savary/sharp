<?php

namespace YonisSavary\Sharp\Commands\Configuration;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Utils;

class BackupConfig extends AbstractCommand
{
    public function __invoke(Args $args)
    {
        $currentConfig = Utils::relativePath('sharp.json');

        if (!is_file($currentConfig))
            return $this->log('No configuration to backup');

        $copyBasename =
            'sharp-json-'.
            substr(md5_file($currentConfig), 0, 4).
            '-'.
            time().
            '.json';

        $copyPath = Storage::getInstance()->path($copyBasename);

        copy($currentConfig, $copyPath);
        $this->log("Configuration backup written to ./Storage/$copyBasename");
    }

    public function getHelp(): string
    {
        return 'Create a backup file of your configuration';
    }
}