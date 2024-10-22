<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Utils\AppMap;
use YonisSavary\Sharp\Core\Utils;

class CreateMap extends Command
{
    public function __invoke(Args $args)
    {
        $application = Terminal::chooseApplication();

        $name = $args->values()[0] ?? readline("Map Name ?");
        if (!preg_match("/^([A-Z][a-zA-Z0-9]*)+$/", $name))
            return $this->log("Given name must be made of PascalName words");
        $filename = $name . ".php";

        $mapsStorage = new Storage(Utils::joinPath($application, "Classes/App/Maps"));
        if ($mapsStorage->isFile($filename))
            return $this->log("$filename already exists !");

        $applicationNamespace = str_replace("/", "\\", $application);

        $mapsStorage->write($filename, Terminal::stringToFile(
            "<?php

            namespace $applicationNamespace\\Classes\\App\\Maps;

            use ".AppMap::class.";

            class $name
            {
                use AppMap;
            }
        "));

        $this->log("File written at : " . $mapsStorage->path($filename));
    }

    public function getHelp(): string
    {
        return "Create a AppMap utility class in your application";
    }
}