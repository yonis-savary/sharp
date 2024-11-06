[< Back to summary](../README.md)

# 🧰 Build system

**It is advised that your read the [CLI Commands](./commands.md) documentation before diving into this one**

Sharp got the `build` command, which can launch custom build tasks in your application/framework

Usage
```bash
php do build # One time build + test
php do build (-w|--watch) # Watch for changes in your files
```

## Create a build task

The `build` command's purpose is to launch what's called "build tasks".

A build task is a class that implements [`AbstractBuildTask`](../../src/Classes/CLI/AbstractBuildTask.php).
Here is an example

```php
class MyCssCompiler extends AbstractBuildTask
{
    // An abstract build task must implement execute(), which return an exit code (0 = success, 1..n = error)
    public function execute(): int
    {
        $this->log("Building stylesheet...");

        $styleDir = Utils::relativePath('MyApp/Assets/less');
        $command = str_starts_with(PHP_OS, "WIN") ? "lessc.cmd" : "lessc";

        $this->shellInDirectory("$command main.less ../css/assets-kit/style.css --verbose", $styleDir, true);
        return 0;
    }
}
```

You may have noticed the use of `$this->log` and `$this->shellInDirectory`, thoses are method from the
[`CLIUtils`](../../src/Classes/CLI/CLIUtils.php) utility class, which got useful methods such as
- `log(string ...$mixed)`
- `shellInDirectory(string $command, string $directory, bool $log=true): void`
- `executeInDirectory(callable $function, string $directory): void`
- `progressBar(array $array, callable $callback, int $progressBarSize=40, string $filledChar='█', string $emptyChar='░')`
- `getMainApplicationPath(Configuration $configuration=null): string`


[< Back to summary](../README.md)
