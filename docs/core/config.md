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

Your application(s) configuration is stored inside `sharp.json`,

(You can also put your environment variables in `env.json`, it shall be merged with the content of `sharp.json`)

The configuration is written as

```json
{
    "snake-case-component-name": {
        "option-name": "some-value"
    }
}
```

> [!NOTE]
> Every component's configuration is described in their respective documentation

If your configuration is missing some keys, or if you want to create one from nothing, you can execute

```bash
php do fill-configuration
```

This will complete your actual configuration with every possible default configuration




## Loading an application

Let's say your application is in a directory named `ShippingApp`, to load it,
you only have to add this in your configuration

```json
"applications": [
    "ShippingApp"
]
```


> [!IMPORTANT]
> If your application contains a `vendor/autoload.php` file,
> it will be automatically required by the autoloader

Now, let's say that you want to make a module for your application named `ShippingCRM` (located in `ShippingApp/ShippingCRM`) then, you will need to add it in your configuration too

```json
"applications": [
    "ShippingApp",
    "ShippingApp/ShippingCRM"
]
```

This feature allows you to extends your application and disable any part of it just by editing your configuration

Also, you can also use the `enable-application` command to add applications in your configuration

```bash
php do enable-application ShippingApp/ShippingCRM
# This means you can also use *
php do enable-application ShippingApp/Modules/*
```

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