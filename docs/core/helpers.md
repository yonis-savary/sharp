[< Back to summary](../README.md)

# 🌐 Code Helpers

Sharp got some helpers files that are included when the framework is loaded.

Those files contains shortcuts to basic framework operations (like adding a route, logging information... etc.)

## Files

This list will describe which file contains which function, you can click on any file name to see its content

```php
# Session helpers
function session          (string $key): mixed;
function sessionSet       (string $key, mixed $value): void;

# Cache helpers
function cache            (string $key, mixed $default=false): mixed;
function cacheSet         (string $key, mixed $value, int $timeToLive=3600*24): void;

# Debug helpers
function sharpDebugMeasure(callable $callback, string $label='Measurement'): void;

# Database helpers
function buildQuery       (string $query, array $context=[]): string;
function query            (string $query, array $context=[]): array;
function lastInsertId(): int|false

# Event helpers
function onEvent          (string $event, callable ...$callbacks): void;
function dispatch         (string $event, mixed ...$args): void;


# Logging helpers
function debug    (mixed ...$messages): void;
function info     (mixed ...$messages): void;
function notice   (mixed ...$messages): void;
function warning  (mixed ...$messages): void;
function error    (mixed ...$messages): void;
function critical (mixed ...$messages): void;
function alert    (mixed ...$messages): void;
function emergency(mixed ...$messages): void;


# Storage helpers
function storePath            (string $path): string;

function storeGetSubStorage   (string $path): Storage;
function storeGetStream       (string $path, string $mode='r', bool $autoclose=true): mixed;

function storeWrite           (string $path, string $content, int $flags=0): void;
function storeRead            (string $path): string;

function storeIsFile          (string $path): bool;
function storeIsDirectory     (string $path): bool;

function storeMakeDirectory   (string $path): void;
function storeRemoveDirectory (string $path): bool;
function storeUnlink          (string $path): bool;

function storeExploreDirectory(string $path, int $mode=Storage::NO_FILTER): array;
function storeListFiles       (string $path='/'): array;
function storeListDirectories (string $path='/'): array;



# Rendering helpers
function asset   (string $target): string;
function script  (string $target, bool $inject=false): string;
function style   (string $target, bool $inject=false): string;

function render  (string $templateName, array $vars=[]): string;
function template(string $templateName, array $context=[]);
function section (string $sectionName): ?string;
function start   (string $sectionName): void;
function stop    (): void;


# Routing helpers
function addRoutes    (Route ...$routes): void;
function addGroup     (array $group, Route ...$routes): void;
function groupCallback(array $group, callable $routeDeclaration): void;
function createGroup  (string|array $urlPrefix, string|array $middlewares): array;


# Authentication helpers
function authIsLogged(): bool
function authGetUser(): array
function authAttempt(string $login, string $password): bool
function authLogout(): void


# CSRF helpers
function csrfToken(): string
function csrfInput(): string


# Scheduling helpers
function scheduleEveryMinute(callable $callback, string $identifier)
function scheduleEveryHour  (callable $callback, string $identifier)
function scheduleEveryDay   (callable $callback, string $identifier)
function scheduleEveryMonth (callable $callback, string $identifier)
function scheduleTwiceADay  (callable $callback, string $identifier)
function scheduleOnMonday   (callable $callback, string $identifier)
function scheduleOnTuesday  (callable $callback, string $identifier)
function scheduleOnWednesday(callable $callback, string $identifier)
function scheduleOnThursday (callable $callback, string $identifier)
function scheduleOnFriday   (callable $callback, string $identifier)
function scheduleOnSaturday (callable $callback, string $identifier)
function scheduleOnSunday   (callable $callback, string $identifier)
```

[< Back to summary](../README.md)