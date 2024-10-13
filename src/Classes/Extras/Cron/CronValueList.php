<?php

namespace YonisSavary\Sharp\Classes\Extras;

use YonisSavary\Sharp\Classes\Data\ObjectArray;

class CronValueList extends AbstractCronExpressionPart
{
    protected ObjectArray $values;

    public function __construct(string $cronExpression, CronTimeType $type)
    {
        $this->type = $type;
        $this->values = ObjectArray::fromExplode(",", $cronExpression)->asIntegers();
        $this->values->forEach(fn($x) => CronTimeType::assertValueIsInRange($x, $type));
    }

    public function valueIsValid(int $value): bool
    {
        return $this->values->includes($value);
    }

    public function toSentence(): string
    {
        $values = $this->values->collect();
        $valuesCount = count($values);
        $firstValues = array_slice($values, 0, $valuesCount-1);
        $lastValue = array_slice($values, $valuesCount-1);

        return "at " . join(", ", $firstValues) . " and " . $lastValue . " " . CronTimeType::toString($this->type);
    }
}