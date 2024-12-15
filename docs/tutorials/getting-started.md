[< Back to summary](../README.md)

# ✅ Getting started

Before making your application, please make sure that you respect these requirements:
- `PHP 8 or more installed`
- `composer installed`

## Installing Sharp

Before installing Sharp, initialize your project with composer

```
composer init
```

Then, you can add the Sharp library

```
composer require yonis-savary/sharp
```

If you plan to make a web application or use Sharp's command, you will need to copy basic utilities scripts from Sharp such as `Public/index.php` and the `do` script (which can be used to launch commands).

```bash
# linux
cp -r ./vendor/yonis-savary/sharp/src/Core/Server/* .

# windows
xcopy /s ./vendor/yonis-savary/sharp/src/Core/Server/* .
```

Note : if you are using Sharp for scripting purpose, you will need to require `vendor/autoload.php` in your script.

## Creating your application

They are two ways of creating an application, manual one and automatic one, we'll explain the manual one first as the automatic one is only applying the same steps

To create your application, make a directory named with a name in `PascalFormat`

```bash
mkdir MyFirstApp
```

Then, create a `sharp.php` file in your **project root** (where your `composer.json` file is), and put this inside

```php
return [
    new ApplicationsToLoad([
        "MyFirstApp"
    ])
];
```

This tells Sharp that the `MyFirstApp` is treated as an application and should be loaded when launching Sharp

Now, if you want to automatically create an application, you can launch this command

```bash
php do create-application MySecondApp
```

## Making your first routes

There is two way to create a route
- By creating a PHP file in `YourApp/Routes`
- By creating a controller class

### Non-controller routes

Let's create our first route in `MyFirstApp/anyName.php`
```php
// A $router variable is provided by default
// otherwise, you can create your own it
// $router = Router::getInstance();

$router->addRoutes(
    Route::get('/hello', function(){
        return 'Hello !';
    }),

    Route::get('/goodbye', fn() => 'Goodbye !')
);
```

to test these two routes, launch the PHP built-in server first

```bash
php do serve
```

Now, let's create the two sames routes in a controller

`MyFirstApp/Controllers/HelloController.php`

```php
namespace MyFirstApp\Controllers;

use YonisSavary\Sharp\Classes\Web\Controller;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;

class HelloController
{
    use Controller;

    public static function declareRoutes(Router $router)
    {
        $router->addRoutes(
            Route::get('/hello', [self::class, 'sayHello']),
            Route::get('/goodbye', [self::class, 'sayHello'])
        );
    }

    public static function sayHello()
    {
        return 'Hello !';
    }

    public static function sayGoodbye()
    {
        return 'Goodbye !';
    }
}
```

## More on routing

**To explore more on routing, you can read the [Routing documentation](../logic/routing.md)**


[< Back to summary](../README.md)
