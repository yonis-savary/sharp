<?php

namespace YonisSavary\Sharp\Classes\Data;

/**
 * This class purpose's is to modelize a database table field
 */
class DatabaseField
{
    const STRING  = 0;
    const INTEGER = 1;
    const FLOAT   = 2;
    const BOOLEAN = 3;
    const DECIMAL = 4;

    const IS_UNIQUE = 5;

    public int $type = self::STRING;
    public bool $nullable = true;
    public bool $unique = false;

    public ?array $reference = null;

    public bool $hasDefault = true;

    public bool $isGenerated = false;

    public function __construct(
        public string $name
    ){}

    public function setType(int $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function hasDefault(bool $hasDefault): self
    {
        $this->hasDefault = $hasDefault;
        return $this;
    }

    public function references(string $table, string $field): self
    {
        $this->reference = [$table, $field];
        return $this;
    }

    public function isGenerated(): self
    {
        $this->isGenerated = true;
        return $this;
    }

    public function validate(mixed $value): bool
    {
        if ($value === null)
            return true;

        return match($this->type) {
            self::DECIMAL   => is_numeric($value),
            self::FLOAT     => is_numeric($value),
            self::INTEGER   => is_numeric($value),
            self::BOOLEAN   => in_array(strtolower("$value"), ["1", "0", "true", "false"]),
            default         => true
        };
    }

    public function validateUpdate(mixed $value): bool
    {
        if ($value !== null)
            return $this->validate($value);

        if (!$this->nullable)
            return false;

        return true;
    }
}