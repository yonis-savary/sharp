<?php

namespace YonisSavary\Sharp\Core;

use InvalidArgumentException;
use RuntimeException;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Cache;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Classes\Events\FailedAutoload;
use Throwable;

class Autoloader
{
    /** Files in this list are available to the autoloader but not directly required */
    const AUTOLOAD = 'autoload';

    /** Files in this list are considered as Views/Templates */
    const VIEWS = 'views';

    /** Files in this list are not PHP file but resources like JS, CSS... */
    const ASSETS = 'assets';

    /** Files in this list are directly required by the autoloader */
    const REQUIRE = 'require';

    /** Files in this list are not directly required by the autoloader but can be by other classes (like `Router`) */
    const ROUTES = 'routes';

    /**
     * This constant old the different purpose of
     * an application directory
     */
    const DIRECTORIES_PURPOSE = [
        'Assets'      => self::ASSETS,
        'Commands'    => self::AUTOLOAD,
        'Controllers' => self::AUTOLOAD,
        'Middlewares' => self::AUTOLOAD,
        'Components'  => self::AUTOLOAD,
        'Classes'     => self::AUTOLOAD,
        'Models'      => self::AUTOLOAD,
        'Routes'      => self::ROUTES,
        'Views'       => self::VIEWS,
        'Helpers'     => self::REQUIRE,
    ];

    const CACHE_FILE = 'autoload.php.cache';

    /**
     * Hold the absolute path to the project root
     * (Nor Public or Sharp directory, but the parent)
     */
    protected static ?string $projectRoot = null;

    /**
     * This variable holds directories path with
     * `Purpose => [...directories]`
     */
    protected static array $lists = [];

    /** Used to cache the results of `getClassesList()` */
    protected static array $cachedClassList = [];

    protected static array $loadedApplications = [];

    public static function initialize()
    {
        self::findProjectRoot();
        self::loadApplications();
    }

    /**
     * Try to find the project root directory, crash on failure
     */
    protected static function findProjectRoot()
    {
        if (self::$projectRoot)
            return self::$projectRoot;

        if (isset($GLOBALS['sharp-root']))
            return self::$projectRoot = $GLOBALS['sharp-root'];

        $original =  getcwd();

        try
        {
            while (!is_dir('vendor/yonis-savary/sharp'))
                chdir('..');

            self::$projectRoot = Utils::normalizePath(getcwd());
        }
        catch (Throwable)
        {
            throw new RuntimeException('Cannot find Sharp project root directory !');
        }

        chdir($original);
    }

    public static function projectRoot(): string
    {
        return self::$projectRoot;
    }

    protected static function loadApplications()
    {
        if (!self::loadAutoloadCache())
        {
            $config = Configuration::getInstance();
            $applications = $config->toArray('applications', []);

            // The framework is loaded as an application
            if ($customSrc = $GLOBALS['sharp-src'] ?? false)
                self::loadApplication($customSrc);
            else
                array_unshift($applications, 'vendor/yonis-savary/sharp/src');

            foreach ($applications as $app)
                self::loadApplication(Utils::relativePath($app), false);
        }

        foreach (self::getList(self::REQUIRE) as $file)
            require_once $file;
    }

    public static function loadApplication(string $path, bool $requireHelpers=true)
    {
        if (!is_dir($path))
            throw new InvalidArgumentException("[$path] is not a directory !");

        self::$loadedApplications[] = $path;

        $vendorFile = Utils::joinPath($path, 'vendor/autoload.php');
        if (is_file($vendorFile))
            self::addToList(self::REQUIRE, $vendorFile);

        foreach (Utils::listDirectories($path) as $directory)
        {
            $basename = basename($directory);

            if (!$purpose = self::DIRECTORIES_PURPOSE[$basename] ?? false)
                continue;

            self::addToList($purpose, $directory);
        }
    }

    public static function getLoadedApplications(): array
    {
        return self::$loadedApplications;
    }

    public static function addToList(string $purposeName, string ...$directoriesOrFiles): void
    {
        self::$lists[$purposeName] ??= [];
        $list = &self::$lists[$purposeName];

        foreach ($directoriesOrFiles as $directoryOrFile)
        {
            if (is_file($directoryOrFile))
                $list[] = $directoryOrFile;
            else
                array_push($list, ...Utils::exploreDirectory($directoryOrFile, Utils::ONLY_FILES));
        }
    }

    public static function getList(string $name): array
    {
        return self::$lists[$name] ?? [];
    }

    /**
     * @param bool $forceReload By default, the result is cached but you can force the function to refresh it
     * @return array Classes list of your applications (Controllers, Models, Components, Classes...etc)
     */
    public static function getClassesList(bool $forceReload=false): array
    {
        if (self::$cachedClassList && !$forceReload)
            return self::$cachedClassList;

        $files = self::getList(self::AUTOLOAD);

        ObjectArray::fromArray($files)
        ->forEach(fn($file) => require_once $file);

        self::$cachedClassList = get_declared_classes();
        return self::$cachedClassList;
    }

    /**
     * Filter every applications classes by passing them to a callback
     * @param callable $filter Filter callback, return `true` to accept a class, `false` to ignore it
     * @return array Filtered class list
     */
    public static function filterClasses(callable $filter): array
    {
        return ObjectArray::fromArray(self::getClassesList())
        ->filter($filter)
        ->collect();
    }

    /**
     * @return array List of classes that implements `$interface` interface
     */
    public static function classesThatImplements(string $interface): array
    {
        return self::filterClasses(fn($e)=> Utils::implements($e, $interface));
    }

    /**
     * @return array List of classes that extends `$class` parent class
     */
    public static function classesThatExtends(string $class): array
    {
        return self::filterClasses(fn($e)=> Utils::extends($e, $class));
    }

    /**
     * @return array List of classes that use the `$trait` Trait
     */
    public static function classesThatUses(string $trait): array
    {
        return self::filterClasses(fn($e)=> Utils::uses($e, $trait));
    }

    public static function loadAutoloadCache(): bool
    {
        $cacheFile = Cache::getInstance()
        ->getStorage()
        ->path(self::CACHE_FILE);

        if (!is_file($cacheFile))
            return false;

        list(
            self::$lists,
            self::$cachedClassList
        ) = include($cacheFile);

        return true;
    }

    public static function writeAutoloadCache()
    {
        $toString = function($var) {
            if (Utils::isAssoc($var))
            {
                foreach ($var as $key => &$value)
                    $value = "'$key' => ['".join("','", $value)."']";
            }
            else
            {
                $var = array_map(fn($e) => "'$e'", $var);
            }

            return '['.join(',', array_values($var)).']';
        };

        // Calling `getListFiles` to cache every possible results
        foreach (array_keys(self::$lists) as $key)
            self::getList($key);

        $cacheFile = Cache::getInstance()->getStorage()->path(self::CACHE_FILE);
        file_put_contents($cacheFile, Terminal::stringToFile(
        "<?php

        return [".join(",", [
            $toString(self::$lists),
            $toString(self::$cachedClassList),
        ]).'];', 2));
    }
}