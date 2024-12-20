<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Classes\Web\Controller;
use YonisSavary\Sharp\Classes\Web\MiddlewareInterface;
use YonisSavary\Sharp\Core\Utils;

class CreateMiddleware extends AbstractCommand
{
    use Controller;

    public function execute(Args $args): int
    {
        $names = $args->values();
        if (!count($names))
            $names = [readline('Middleware name (PascalCase) > ')];

        $application = Terminal::chooseApplication();

        foreach ($names as $name)
        {
            if (!preg_match("/^[A-Z][\d\w]*$/", $name))
            {
                $this->log('Name be must a PascalCase string');
                return 2;
            }

            $middlewarePath = Utils::joinPath($application, 'Middlewares');
            $storage = new Storage($middlewarePath);
            $filename = $name . '.php';

            if ($storage->isFile($name))
            {
                $this->log($storage->path($filename) . ' already exists !');
                return 1;
            }

            $storage->write($filename, Terminal::stringToFile(
            "<?php

            namespace ".Utils::pathToNamespace($middlewarePath).";

            use ". MiddlewareInterface::class .";
            use ". Request::class .";
            use ". Response::class .";

            class $name implements MiddlewareInterface
            {
                public static function handle(Request \$request): Request|Response
                {
                    return \$request;
                }
            }
            "));

            $this->log('File written at '. $storage->path($filename));
            return 0;
        }
    }

    public function getHelp(): string
    {
        return 'Create middlewares(s) inside your application';
    }
}