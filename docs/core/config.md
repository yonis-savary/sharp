[< Back to summary](../README.md)

# 📦 Setup & Configuration

## Application Directory

Sharp's autoloader is made to load multiples applications at the same time (which mean that you can split a big application into modules/services)

One application is made of those directories (All of them are optional):
- Assets
- Classes
- Controllers
- Commands
- Components
- Features
- Others
- Routes
- Helpers
- Views

> [!NOTE]
> Files in `Helpers` and `Others` are recursively included with `require_once`

## Configuration

Your application(s) configuration is stored inside `sharp.php`,
which is a script that return a set of configuration objects

Example :

```php
return [
    new ApplicationToLoad([
        "FirstApp",
        "SomeModule"
    ])
];
```

> [!NOTE]
> Every component's configuration is described in their respective documentation

If you want to make a default configuration for your application, you can execute

```bash
php do create-configuration
```

This will create a new configuration with every possible default configuration
(it also make a backup of your current configuration)




## Loading an application

Let's say your application is in a directory named `ShippingApp`, to load it,
you only have to add this in your configuration

```php
return [
    new ApplicationToLoad([
        "ShippingApp"
    ])
];
```


> [!IMPORTANT]
> If your application contains a `vendor/autoload.php` file,
> it will be automatically required by the autoloader

Now, let's say that you want to make a module for your application named `ShippingCRM` (located in `ShippingApp/ShippingCRM`) then, you will need to add it in your configuration too

```php
return [
    new ApplicationToLoad([
    "ShippingApp",
    "ShippingApp/ShippingCRM"
    ])
];
```

This feature allows you to extends your application and disable any part of it just by editing your configuration

> [!IMPORTANT]
> Applications are loaded in the order they're written in your configuration
> (Beware of dependencies !)


## Making custom script that uses Sharp

If you want to use YonisSavary\Sharp in a PHP script, you only have to require `vendor/autoload.php`

Including this script will load the components without doing anything else

## Additional properties

- `Autoloader::getList(Autoloader::AUTOLOAD)` can retrieve files in
    - Commands
    - Controllers
    - Classes
    - Components
    - Features
    - Models
- `Autoloader::getList(Autoloader::ASSETS)` can retrieve files in
    - Assets
- `Autoloader::getList(Autoloader::VIEWS)` can retrieve files in
    - Views
- `Autoloader::getList(Autoloader::ROUTES)` can retrieve files in
    - Routes
- `Autoloader::getList(Autoloader::REQUIRE)` can retrieve files in
    - Helpers
    - Others


[< Back to summary](../README.md)