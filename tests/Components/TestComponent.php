<?php

namespace YonisSavary\Sharp\Tests\Components;

use YonisSavary\Sharp\Classes\Core\Component;

class TestComponent
{
    use Component;

    protected string $name;

    public function __construct(string $name=null)
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public static function getDefaultInstance(): static
    {
        return new self("default");
    }
}