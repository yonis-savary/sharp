<?php

namespace YonisSavary\Sharp\Classes\Env\Configuration;

use Error;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Core\Utils;

class Configuration
{
    use Component;

    protected array $elements = [];
    protected array $genericElements = [];

    public static function getDefaultInstance(): static
    {
        if (is_file(Utils::relativePath("sharp.php")))
            return new self("sharp.php");

        return new self();
    }

    public static function fromFile(string $file): self
    {
        return new self($file);
    }

    protected static function loadConfiguration(string $fileToLoad)
    {
        $fileToLoad = is_file($fileToLoad) ? $fileToLoad : Utils::relativePath($fileToLoad);
        if (!is_file($fileToLoad))
        {
            Logger::getInstance()->error("Could not load $fileToLoad (not a file)");
            return [];
        }

        $newElements = require Utils::relativePath($fileToLoad);
        if ((!is_array($newElements)) || (Utils::isAssoc($newElements)))
            throw new Error("A configuration file must return an array of configuration element (loading $fileToLoad)");

        $filtered = [];
        foreach ($newElements as $element)
        {
            if ($element instanceof Configuration)
                array_push($filtered, ...$element->getElements());
            else
                $filtered[] = $element;
        }

        return $filtered;
    }

    public function __construct(string $fileToLoad=null)
    {
        if ($fileToLoad)
            $this->mergeWithFile($fileToLoad);
    }

    public function mergeWithFile(string $fileToLoad)
    {
        $this->elements = array_merge(
            self::loadConfiguration($fileToLoad),
            $this->elements
        );

        $this->refreshGenericElements();
    }

    public function refreshGenericElements()
    {
        $this->genericElements = [];

        foreach ($this->elements as &$element)
        {
            if ($element instanceof GenericConfiguration)
                $this->genericElements[] = &$element;
        }
    }


    public function resolve(string $class, mixed $default=null)
    {
        return
            ObjectArray::fromArray($this->elements)
            ->find(fn($x) => $x::class == $class)
            ??
            $default;
    }

    public function resolveByName(string $targetedName, mixed $default=null)
    {
        if ($found = ObjectArray::fromArray($this->genericElements)
            ->find(fn(GenericConfiguration $x) => $x->name === $targetedName)
        )
            return $found->value;

        return $default;
    }


    public function addElements(...$elements)
    {
        array_unshift($this->elements, ...$elements);
        $this->refreshGenericElements();
    }


    public function getElements(): array
    {
        return $this->elements;
    }
}