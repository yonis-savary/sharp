<?php

namespace YonisSavary\Sharp\Tests\Units;

use DateTime;
use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Extras\Cron\CronExpression;
use YonisSavary\Sharp\Classes\Extras\Scheduler;

class SchedulerTest extends TestCase
{
    public function test_schedulerDateValidator()
    {
        $pairs = [
            // Reminder
            //                       ┌─────── minute 0-59
            //                       | ┌─────── hours 0-23
            //                       | | ┌─────── day 1-31
            //                       | | | ┌─────── month 1-12
            //                       | | | | ┌─────── weekday 0-6
            //                       | | | | |
            ['2000-10-01 16:00:00', '* * * * *', true], // is a sunday(0)
            ['2000-10-01 16:00:00', '* * * * *', true],
            ['2000-10-01 16:00:00', '0 * * * *', true],
            ['2000-10-01 16:00:00', '* * * * 0', true],
            ['2000-10-01 16:00:00', '* * * 10 *', true],
            ['2000-10-01 16:00:00', '* 9-18 * * *', true],
            ['2000-10-01 16:00:00', '* 9-15 * * *', false],

            ['2000-07-16 08:52:00', '52-59 * * 05-07 *', true],
            ['2000-07-16 08:52:00', '52 8 16 7 0', true],
            ['2000-07-16 08:52:00', '* * * * *', true],
            ['2000-07-16 08:52:00', '* * * * 1', false],
            ['2000-07-16 08:52:00', '* * 20-31 * *', false],
        ];

        foreach ($pairs as [$date, $cron, $result])
        {
            $datetime = new DateTime($date);
            $expression = new CronExpression($cron);
            $this->assertEquals($result, $expression->matches($datetime));
        }
    }


    public function test_weekdayTest()
    {
        $scheduler = new Scheduler;

        $mondayCount   = 0;
        $tuesdayCount  = 0;
        $wednesdayCount= 0;
        $thursdayCount = 0;
        $fridayCount   = 0;
        $saturdayCount = 0;
        $sundayCount   = 0;

        $everyHourCount = 0;
        $everyDayCount = 0;
        $everyMonthCount = 0;
        $everyTwiceADayCount = 0;

        $scheduler->schedule(CRON_EVERY_HOUR  , function() use (&$everyHourCount)      { $everyHourCount++; });
        $scheduler->schedule(CRON_EVERY_DAY   , function() use (&$everyDayCount)       { $everyDayCount++; });
        $scheduler->schedule(CRON_EVERY_MONTH , function() use (&$everyMonthCount)     { $everyMonthCount++; });
        $scheduler->schedule(CRON_TWICE_A_DAY , function() use (&$everyTwiceADayCount) { $everyTwiceADayCount++; });
        $scheduler->schedule(CRON_ON_MONDAY   , function() use (&$mondayCount   )      { $mondayCount++; });
        $scheduler->schedule(CRON_ON_TUESDAY  , function() use (&$tuesdayCount  )      { $tuesdayCount++; });
        $scheduler->schedule(CRON_ON_WEDNESDAY, function() use (&$wednesdayCount)      { $wednesdayCount++; });
        $scheduler->schedule(CRON_ON_THURSDAY , function() use (&$thursdayCount )      { $thursdayCount++; });
        $scheduler->schedule(CRON_ON_FRIDAY   , function() use (&$fridayCount   )      { $fridayCount++; });
        $scheduler->schedule(CRON_ON_SATURDAY , function() use (&$saturdayCount )      { $saturdayCount++; });
        $scheduler->schedule(CRON_ON_SUNDAY   , function() use (&$sundayCount   )      { $sundayCount++; });

        $pointer = new DateTime('2024-07-01 00:00:00');
        $end     = new DateTime('2024-07-31 23:59:59');

        do
        {
            $scheduler->executeAll($pointer);
            $pointer->modify('+1 hour');
        } while ($pointer <= $end);

        $this->assertEquals(24*31, $everyHourCount);
        $this->assertEquals(31, $everyDayCount);
        $this->assertEquals(1, $everyMonthCount);
        $this->assertEquals(31*2, $everyTwiceADayCount);

        $this->assertEquals(5, $mondayCount);
        $this->assertEquals(5, $tuesdayCount);
        $this->assertEquals(5, $wednesdayCount);
        $this->assertEquals(4, $thursdayCount);
        $this->assertEquals(4, $fridayCount);
        $this->assertEquals(4, $saturdayCount);
        $this->assertEquals(4, $sundayCount);

        $this->assertTrue(true);
    }
}