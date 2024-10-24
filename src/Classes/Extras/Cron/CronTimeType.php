<?php

namespace YonisSavary\Sharp\Classes\Extras\Cron;

use InvalidArgumentException;

enum CronTimeType
{
    case MINUTE;
    case HOUR;
    case DAY;
    case MONTH;
    case WEEKDAY;

    public static function toString(CronTimeType $type)
    {
        switch ($type)
        {
            case self::MINUTE:  return 'minute';
            case self::HOUR:    return 'hour';
            case self::DAY:     return 'day';
            case self::MONTH:   return 'month';
            case self::WEEKDAY: return 'weekday';
        }
    }

    public static function getValueString(int $value, CronTimeType $type)
    {
        $monthsLabels = [null, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $weekdayLabels = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        switch ($type)
        {
            case self::MINUTE:  return $value;
            case self::HOUR:    return $value;
            case self::DAY:     return $value;
            case self::MONTH:   return $monthsLabels[$value];
            case self::WEEKDAY: return $weekdayLabels[$value];
        }
    }


    public static function assertValueIsInRange(int $value, CronTimeType $type)
    {
        if ($type === self::WEEKDAY)
            $value = $value % 7;

        $throwIfOutOfRange = function($min, $max) use (&$value, &$type) {
            if (!($min <= $value && $value <= $max))
                throw new InvalidArgumentException('Cron value of type ' . self::toString($type). " must be between $min and $max");
        };

        switch ($type)
        {
            case self::MINUTE:
                $throwIfOutOfRange(0, 59);
                break;
            case self::HOUR:
                $throwIfOutOfRange(0, 23);
                break;
            case self::DAY:
                $throwIfOutOfRange(1, 31);
                break;
            case self::MONTH:
                $throwIfOutOfRange(1, 12);
                break;
            case self::WEEKDAY:
                $throwIfOutOfRange(0, 6);
                break;
        }
    }
}
