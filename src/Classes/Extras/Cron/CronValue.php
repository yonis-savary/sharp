<?php

namespace YonisSavary\Sharp\Classes\Extras\Cron;

class CronValue extends AbstractCronExpressionPart
{
    protected int $value;

    public function __construct(string $cronExpression, CronTimeType $type)
    {
        $this->type = $type;
        $this->value = (int) $cronExpression;
        CronTimeType::assertValueIsInRange($this->value, $type);
    }

    public function valueIsValid(int $value): bool
    {
        return $value == $this->value;
    }

    public function toSentence(): string
    {
        $type = $this->type;
        $valueString = CronTimeType::getValueString($this->value, $type);

        if ($type == CronTimeType::WEEKDAY)
            return "the $valueString";

        return "at $valueString " . CronTimeType::toString($this->type);
    }

}