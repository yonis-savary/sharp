<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Utils\AppCache;
use YonisSavary\Sharp\Core\Utils;

class CreateCache extends AbstractCommand
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

        $cacheStorage = new Storage(Utils::joinPath($application, 'Classes/App/Caches'));
        if ($cacheStorage->isFile($filename))
        {
            $this->log("$filename already exists !");
            return 1;
        }

        $applicationNamespace = str_replace('/', "\\", $application);

        $cacheStorage->write($filename, Terminal::stringToFile(
            "<?php

            namespace $applicationNamespace\\Classes\\App\\Caches;

            use ".AppCache::class.";

            class $name
            {
                use AppCache;
            }
        "));

        $this->log('File written at : ' . $cacheStorage->path($filename));
        return 0;
    }


    public function getHelp(): string
    {
        return 'Create a AppCache utility class in your application';
    }
}