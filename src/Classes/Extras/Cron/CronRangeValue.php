<?php

namespace YonisSavary\Sharp\Classes\Extras\Cron;

class CronRangeValue extends AbstractCronExpressionPart
{
    protected int $min;
    protected int $max;

    public function __construct(string $cronExpression, CronTimeType $type)
    {
        $this->type = $type;

        list($min, $max) = explode('-', $cronExpression);

        $this->min = (int) $min;
        $this->max = (int) $max;

        CronTimeType::assertValueIsInRange($this->min, $type);
        CronTimeType::assertValueIsInRange($this->max, $type);
    }

    public function valueIsValid(int $value): bool
    {
        return $this->min <= $value && $value <= $this->max;
    }

    public function toSentence(): string
    {
        $type = $this->type;
        return
            'every ' . CronTimeType::toString($this->type) .
            ' from ' . CronTimeType::getValueString($this->min, $type) .
            ' to ' . CronTimeType::getValueString($this->max, $type)
            ;
    }
}