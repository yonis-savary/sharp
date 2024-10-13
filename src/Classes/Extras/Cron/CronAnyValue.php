<?php

namespace YonisSavary\Sharp\Classes\Extras;

class CronAnyValue extends AbstractCronExpressionPart
{
    public function __construct(string $cronExpression, CronTimeType $type)
    {
        $this->type = $type;
    }

    public function valueIsValid(int $value): bool
    {
        return true;
    }

    public function toSentence(): string
    {
        return "every " . CronTimeType::toString($this->type);
    }
}