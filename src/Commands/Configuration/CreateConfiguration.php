<?php

namespace YonisSavary\Sharp\Commands\Configuration;

use ReflectionClass;
use ReflectionParameter;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class CreateConfiguration extends AbstractCommand
{
    public function getHelp(): string
    {
        return "Create or Complete your configuration with the framework's default configuration";
    }

    public function getDefaultValueAsString(mixed $value)
    {
        if ($value === true)
            return "true";

        if ($value === false)
            return "false";

        if ($value === null)
            return "null";

        if (is_string($value))
            return '"' . str_replace('"', '\\"', $value) . '"';

        if (is_int($value) || is_float($value))
            return $value;

        if (is_array($value))
            return "[". ObjectArray::fromArray($value)->map(fn($x) => $this->getDefaultValueAsString($x))->join(",") . "]";

        if (is_object($value))
            return $this->getClassString(new ReflectionClass($value), true) . " /* experimental support : may contains errors */";

        return "null /* Could not generate string from ".gettype($value)." variable */";
    }

    public function getParameterString(ReflectionParameter $parameter)
    {
        return
            "\t\t".
            $parameter->getName() . ": " . $this->getDefaultValueAsString($parameter->getDefaultValue())
            .",\n"
        ;
    }

    public function getClassString(ReflectionClass $class, bool $useFullName=false): string
    {
        $constructor = $class->getConstructor();
        $classNameString = ($useFullName ? $class->getName() : $class->getShortName());
        if (!$constructor)
            return "new $classNameString()";

        $parameters = ObjectArray::fromArray($constructor->getParameters());

        return
            "\tnew $classNameString(\n" .
                $parameters
                ->map(fn($x) => $this->getParameterString($x))
                ->join("")
            ."\t)";

    }

    public function getDefaultConfigString(): string
    {
        $classes = ObjectArray::fromArray(
            Autoloader::classesThatUses(ConfigurationElement::class)
        )
        ->map(fn($x) => new ReflectionClass($x));

        return
            "<?php".

            "\n\n".

            $classes->map(
                fn(ReflectionClass $x) => "use " . $x->getName() . ";"
            )
            ->join("\n").

            "\n\n".

            "return [\n".
                $classes
                ->map(fn($x) => $this->getClassString($x))
                ->join(",\n\n")
            .
            "\n];";
    }

    public function execute(Args $args): int
    {
        $storage = Storage::getInstance();
        $configPath = Utils::relativePath("sharp.php");

        if (is_file($configPath))
        {
            do
            {
                $backupFile = $storage->path(uniqid(hrtime(true))) . "-sharp.php";
            } while (is_file($backupFile));

            rename($configPath, $backupFile);
            $this->log(
                $this->withYellowColor(
                    "Moved your current configuration to ./".
                    str_replace(Autoloader::projectRoot()."/", "", $backupFile)
                )
            );
        }

        $configString = $this->getDefaultConfigString();
        file_put_contents($configPath, $configString);
        $this->log($this->withBlueColor("New configuration written in sharp.php"));

        return 0;
    }
}