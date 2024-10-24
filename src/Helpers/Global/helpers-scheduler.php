<?php

use YonisSavary\Sharp\Classes\Extras\Scheduler;

const CRON_EVERY_MINUTE = '* * * * *';
const CRON_EVERY_HOUR   = '0 * * * *';
const CRON_EVERY_DAY    = '0 0 * * *';
const CRON_EVERY_MONTH  = '0 0 1 * *';
const CRON_TWICE_A_DAY  = '0 0,12 * * *';
const CRON_ON_MONDAY    = '0 0 * * 1';
const CRON_ON_TUESDAY   = '0 0 * * 2';
const CRON_ON_WEDNESDAY = '0 0 * * 3';
const CRON_ON_THURSDAY  = '0 0 * * 4';
const CRON_ON_FRIDAY    = '0 0 * * 5';
const CRON_ON_SATURDAY  = '0 0 * * 6';
const CRON_ON_SUNDAY    = '0 0 * * 0';

function scheduleEveryMinute(callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_EVERY_MINUTE, $callback, $identifier); }
function scheduleEveryHour  (callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_EVERY_HOUR, $callback, $identifier); }
function scheduleEveryDay   (callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_EVERY_DAY, $callback, $identifier); }
function scheduleEveryMonth (callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_EVERY_MONTH, $callback, $identifier); }
function scheduleTwiceADay  (callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_TWICE_A_DAY, $callback, $identifier); }
function scheduleOnMonday   (callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_ON_MONDAY, $callback, $identifier); }
function scheduleOnTuesday  (callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_ON_TUESDAY, $callback, $identifier); }
function scheduleOnWednesday(callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_ON_WEDNESDAY, $callback, $identifier); }
function scheduleOnThursday (callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_ON_THURSDAY, $callback, $identifier); }
function scheduleOnFriday   (callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_ON_FRIDAY, $callback, $identifier); }
function scheduleOnSaturday (callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_ON_SATURDAY, $callback, $identifier); }
function scheduleOnSunday   (callable $callback, string $identifier) { Scheduler::getInstance()->schedule(CRON_ON_SUNDAY, $callback, $identifier); }