<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Web\Controller;
use YonisSavary\Sharp\Classes\Web\Router;
use YonisSavary\Sharp\Core\Utils;

class CreateController extends AbstractCommand
{
    use Controller;

    public function execute(Args $args): int
    {
        $names = $args->values();
        if (!count($names))
            $names = [readline('Controller name (PascalCase) > ')];

        $application = Terminal::chooseApplication();

        $gotError = false;

        foreach ($names as $name)
        {
            if (!preg_match("/^[A-Z][\d\w]*$/", $name))
            {
                $this->log('Name be must a PascalCase string');
                $gotError = true;
                continue;
            }

            $controllerPath = Utils::joinPath($application, 'Controllers');
            $storage = new Storage($controllerPath);
            $filename = $name . '.php';

            if ($storage->isFile($name))
            {
                $this->log($storage->path($filename) . ' already exists !');
                $gotError = true;
                continue;
            }

            $storage->write($filename, Terminal::stringToFile(
            "<?php

            namespace ".Utils::pathToNamespace($controllerPath).";

            use ". Controller::class .";
            use ". Router::class .";

            class $name
            {
                use Controller;

                public static function declareRoutes(Router \$router): void
                {
                    \$router->addGroup(
                        [],

                    );
                }
            }
            "));

            $this->log('File written at '. $storage->path($filename));
        }

        return (int) $gotError;
    }

    public function getHelp(): string
    {
        return 'Create controller(s) inside your application';
    }
}