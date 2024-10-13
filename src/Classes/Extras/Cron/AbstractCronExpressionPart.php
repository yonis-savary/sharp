<?php

namespace YonisSavary\Sharp\Classes\Extras;

abstract class AbstractCronExpressionPart
{
    protected CronTimeType $type;

    public abstract function __construct(string $cronExpression, CronTimeType $type);
    public abstract function valueIsValid(int $value): bool;
    public abstract function toSentence(): string;
}