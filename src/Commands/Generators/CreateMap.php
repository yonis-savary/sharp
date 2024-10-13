<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Utils;

class CreateMap extends Command
{
    public function __invoke(Args $args)
    {
        $application = Terminal::chooseApplication();

        $name = $args->values()[0] ?? readline("Map Name ?");
        if (!preg_match("/^([A-Z][a-zA-Z0-9]*)+$/", $name))
            return print("Given name must be made of PascalName words\n");
        $filename = $name . ".php";

        $mapsStorage = new Storage(Utils::joinPath($application, "Classes/App/Maps"));
        if ($mapsStorage->isFile($filename))
            return print("$filename already exists !\n");

        $applicationNamespace = str_replace("/", "\\", $application);

        $mapsStorage->write($filename, Terminal::stringToFile(
            "<?php

            namespace $applicationNamespace\\Classes\\App\\Maps;

            use YonisSavary\Sharp\Classes\Utils\AppMap;

            class $name
            {
                use AppMap;
            }
        "));

        echo "File written at : " . $mapsStorage->path($filename) . "\n";
    }

    public function getHelp(): string
    {
        return "Create a AppMap utility class in your application";
    }
}