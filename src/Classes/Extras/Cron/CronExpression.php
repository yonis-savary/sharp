<?php

namespace YonisSavary\Sharp\Classes\Extras\Cron;

use DateTime;
use InvalidArgumentException;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

class CronExpression
{
    protected ObjectArray $parts;

    public function __construct(string $cronExpression)
    {
        if (!preg_match("/^((\d+|\d+\-\d+|(\d+,?)+|\*) ?){5}$/", $cronExpression))
            throw new InvalidArgumentException("Invalid cron syntax (\"$cronExpression\" do not respect regex)");

        $parts = explode(' ', $cronExpression);

        if (count($parts) != 5)
            throw new InvalidArgumentException('Invalid cron syntax (invalid part count, must be 5, actuall is '.count($parts).')');

        $types = [
            CronTimeType::MINUTE,
            CronTimeType::HOUR,
            CronTimeType::DAY,
            CronTimeType::MONTH,
            CronTimeType::WEEKDAY,
        ];

        for ($i=0; $i<5; $i++)
        {
            $part = &$parts[$i];
            $type = $types[$i];

            if ($part === '*')
                $part = new CronAnyValue($part, $type);
            else if (preg_match("/^\d+$/", $part))
                $part = new CronValue($part, $type);
            else if (preg_match("/^\d+\-\d+$/", $part))
                $part = new CronRangeValue($part, $type);
            else if (preg_match("/^(\d+,?)+$/", $part))
                $part = new CronValueList($part, $type);
            else if (preg_match("/^\d+\/\d+$/", $part))
                throw new InvalidArgumentException("Non-standar step value is not supported [$part]");
            else
                throw new InvalidArgumentException("Unrecognized cron value syntax [$part]");
        }

        $this->parts = ObjectArray::fromArray($parts);
    }

    public function matches(DateTime $datetime): bool
    {
        $datetimeParts = [
            (int)($datetime->format('i')),
            (int)($datetime->format('H')),
            (int)($datetime->format('d')),
            (int)($datetime->format('m')),
            (int)($datetime->format('w')),
        ];

        return $this->parts->all(
            fn(AbstractCronExpressionPart $x, int $index) => $x->valueIsValid($datetimeParts[$index])
        );
    }

    public function toSentence(): string
    {
        return $this->parts
        ->map(fn(AbstractCronExpressionPart $x) => $x->toSentence())
        ->join(', ');
    }
}