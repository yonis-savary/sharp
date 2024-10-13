<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Utils;

class CreateStorage extends Command
{
    public function __invoke(Args $args)
    {
        $application = Terminal::chooseApplication();

        $name = $args->values()[0] ?? readline("Storage Name ?");
        if (!preg_match("/^([A-Z][a-zA-Z0-9]*)+$/", $name))
            return print("Given name must be made of PascalName words\n");
        $filename = $name . ".php";

        $storagePath = new Storage(Utils::joinPath($application, "Classes/App/Storages"));
        if ($storagePath->isFile($filename))
            return print("$filename already exists !\n");

        $applicationNamespace = str_replace("/", "\\", $application);

        $storagePath->write($filename, Terminal::stringToFile(
            "<?php

            namespace $applicationNamespace\\Classes\\App\\Storages;

            use YonisSavary\Sharp\Classes\Utils\AppStorage;

            class $name
            {
                use AppStorage;
            }
        "));

        echo "File written at : " . $storagePath->path($filename) . "\n";
    }

    public function getHelp(): string
    {
        return "Create a AppStorage utility class in your application";
    }
}