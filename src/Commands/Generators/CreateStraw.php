<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Extras\SessionStraw;
use YonisSavary\Sharp\Core\Utils;

class CreateStraw extends Command
{
    protected function createStraw(string $name, string $app)
    {
        if (!preg_match("/^[A-Z][a-zA-Z0-9]*$/", $name))
            return print("Given straw name must be in PascalCase\n");

        $directory = Utils::joinPath($app, "Classes/Straws");
        $file = Utils::joinPath($directory, $name. ".php");

        $namespace = Utils::pathToNamespace($directory);

        if (file_exists($file))
            return print("[$file] file already exists !\n");

        if (!is_dir($directory))
            mkdir($directory, recursive: true);

        file_put_contents($file, Terminal::stringToFile(
        "<?php

        namespace $namespace;

        use ".SessionStraw::class.";

        class $name
        {
            use SessionStraw;
        }
        ", 2));

        return print("File created at [$file]\n");
    }

    public function __invoke(Args $args)
    {
        $values = $args->values();

        if (!count($values))
            $values = [Terminal::prompt("Straw name (PascalCase): ")];

        $app = Terminal::chooseApplication();

        foreach($values as $name)
            $this->createStraw($name, $app);
    }

    public function getHelp(): string
    {
        return "Create a SessionStraw class in your application";
    }
}