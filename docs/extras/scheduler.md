[< Back to Summary](../README.md)

# 🕒 Scheduler

The [`Scheduler`](../../src/Classes/Extras/Scheduler.php) component can help you program tasks that
should execute at some specific intervals

## Add a task to the scheduler

The Scheduler uses the [Cron syntax](https://docs.gitlab.com/ee/topics/cron/) to know when to launch tasks

Use `Scheduler::schedule` to add a task to the scheduler

```php
# Add a simple task that says Hello !
Scheduler::getInstance()->schedule("* * * * *", fn() => print("Hello!"));

# Add a simple task that says Hello ! and name the task "helloPrinter"
Scheduler::getInstance()->schedule("* * * * *", fn() => print("Hello!"), "helloPrinter");
```

then, execute the `scheduler-launch` command to launch every task that respects the current time

```bash
php do scheduler-launch
```

## Logs & Debug

Every scheduled tasks have a unique identifier which helps you debug your tasks

Every time a task is launched, it will print its output and errors in a log file stored in `Storage/Logs/schedule`

## Shorthand & Helpers

Some helpers constants/functions exist to make task definition quicker

```php
$scheduler = Scheduler::getInstance();

# Cron syntax constants
$scheduler->schedule(CRON_EVERY_MINUTE, ...);
$scheduler->schedule(CRON_EVERY_HOUR, ...);
$scheduler->schedule(CRON_EVERY_DAY, ...);
$scheduler->schedule(CRON_EVERY_MONTH, ...);
$scheduler->schedule(CRON_TWICE_A_DAY, ...);
$scheduler->schedule(CRON_ON_MONDAY, ...);
$scheduler->schedule(CRON_ON_TUESDAY, ...);
$scheduler->schedule(CRON_ON_WEDNESDAY, ...);
$scheduler->schedule(CRON_ON_THURSDAY, ...);
$scheduler->schedule(CRON_ON_FRIDAY, ...);
$scheduler->schedule(CRON_ON_SATURDAY, ...);
$scheduler->schedule(CRON_ON_SUNDARY, ...);

# helpers functions (which always need an identifier)
scheduleEveryMinute($callback, $identifier)
scheduleEveryHour($callback, $identifier)
scheduleEveryDay($callback, $identifier)
scheduleEveryMonth($callback, $identifier)
scheduleTwiceADay($callback, $identifier)
scheduleOnMonday($callback, $identifier)
scheduleOnTuesday($callback, $identifier)
scheduleOnWednesday($callback, $identifier)
scheduleOnThursday($callback, $identifier)
scheduleOnFriday($callback, $identifier)
scheduleOnSaturday($callback, $identifier)
scheduleOnSunday($callback, $identifier)
```

## Add the scheduler to your CRON

You can either copy this declaration in your CRON

```cron
* * * * * cd /path/to/your/profile && php do scheduler-launch
```

or launch this command to get a ready-to-use CRON statement

```bash
php do scheduler-generate
```

[< Back to Summary](../README.md)