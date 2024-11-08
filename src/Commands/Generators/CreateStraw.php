<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Extras\SessionStraw;
use YonisSavary\Sharp\Core\Utils;

class CreateStraw extends AbstractCommand
{
    protected function createStraw(string $name, string $app)
    {
        if (!preg_match("/^[A-Z][a-zA-Z0-9]*$/", $name))
        {
            $this->log('Given straw name must be in PascalCase');
            return 2;
        }

        $directory = Utils::joinPath($app, 'Classes/App/Straws');
        $file = Utils::joinPath($directory, $name. '.php');

        $namespace = Utils::pathToNamespace($directory);

        if (file_exists($file))
        {
            $this->log("[$file] file already exists !");
            return 1;
        }

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

        $this->log("File created at [$file]");
        return 0;
    }

    public function execute(Args $args): int
    {
        $values = $args->values();

        if (!count($values))
            $values = [Terminal::prompt('Straw name (PascalCase): ')];

        $app = Terminal::chooseApplication();

        $gotError = false;
        foreach($values as $name)
            $gotError |= $this->createStraw($name, $app);

        return (int) $gotError;
    }

    public function getHelp(): string
    {
        return 'Create a SessionStraw class in your application';
    }
}