<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Utils\AppStorage;
use YonisSavary\Sharp\Core\Utils;

class CreateStorage extends AbstractCommand
{
    public function execute(Args $args): int
    {
        $application = Terminal::chooseApplication();

        $name = $args->values()[0] ?? readline('Storage Name ?');
        if (!preg_match("/^([A-Z][a-zA-Z0-9]*)+$/", $name))
        {
            $this->log('Given name must be made of PascalName words');
            return 2;
        }
        $filename = $name . '.php';

        $storagePath = new Storage(Utils::joinPath($application, 'Classes/App/Storages'));
        if ($storagePath->isFile($filename))
        {
            $this->log("$filename already exists !");
            return 1;
        }

        $applicationNamespace = str_replace('/', "\\", $application);

        $storagePath->write($filename, Terminal::stringToFile(
            "<?php

            namespace $applicationNamespace\\Classes\\App\\Storages;

            use ".AppStorage::class.";

            class $name
            {
                use AppStorage;
            }
        "));

        $this->log('File written at : ' . $storagePath->path($filename));
        return 0;
    }

    public function getHelp(): string
    {
        return 'Create a AppStorage utility class in your application';
    }
}