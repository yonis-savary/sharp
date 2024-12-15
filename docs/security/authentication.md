[< Back to summary](../README.md)

# 🔐 Authentication

Sharp got the [`Authentication`](../../src/Classes/Security/Authentication.php) class to handle authentication

Authentication is made through a [Model](../data/database.md) that you can choose

## Configuration

You have to configure those five parameters in your configuration:

```php
return [
	new AuthenticationConfiguration(
		model: User::class,
		loginField: "login",
		passwordField: "password",
		saltField: null,
		sessionDuration: 3600,
	),
];
```

- `model` is the full classname to your model class
- `login-field` is the name of the unique field in your model
- `password-field` is the name of the field where your password hash is stored
- `salt-field` (optional, can be `null`) is the name of the field where your password salt is stored
- `session-duration` duration of Authentication session in seconds (for example, 3600sec <=> 1 hour, after one hour of inactivity, the user is logged out)

## Usage

```php
$authentication = Authentication::getInstance();

// attempt() tries to log the user
// return true on success, false on failure
if ($authentication->attempt('login', 'password'))
{
    // Success !
}

$authentication->isLogged();

// Array of data if the user is logged, null otherwise
$user = $authentication->getUser();

// Logout the user as reset attempt number
$authentication->logout();

// Number of failed attempts number (reset to 0 when logged in)
$authentication->attemptNumber();
```

## ✅ Tutorial: Setting up Authentication !

### Context

1. Our application name is `MagicShip`
2. We have a `user` table defined as :
```sql
CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    salt VARCHAR(64) NOT NULL
);
```

### Setup

First, we have to ensure our model exists, before
working with our model, let's fetch it

```bash
php do fetch-models
```

This will create `MagicShip/Models/User.php` file containing our model.

Then we must configure authentication before using it

`sharp.php`:
```php
return [
	new AuthenticationConfiguration(
		model: User::class,
		loginField: "login",
		passwordField: "password",
		saltField: "salt",
		sessionDuration: 3600,
	),
];
```

### Authentication

Then we need a route that can handle a login attempt

`MagicShip/Controllers/AuthController.php`:
```php
class AuthController
{
    use Controller;

    public static function declareRoutes()
    {
        Router::getInstance()->addRoutes(
            Route::view('/login', 'user/login'),
            Route::post('/login', [self::class, 'handleLogin']),
            Route::get('/logout', [self::class, 'handleLogout']),
        );
    }

    public static function handleLogin(Request $request): Response
    {
        // Retrieve username & password fields from request body
        list($username, $password) = $request->list('username', 'password');

        if (Authentication::attempt($username, $password))
            return Response::redirect('/');

        return Response::redirect('/?error=bad_login');
    }

    public static function handleLogout(): Response
    {
        Authentication::getInstance()->logout();
        return Response::redirect('/login');
    }
}
```

### Protecting routes with a middleware

Now, we want `/` to be accessible only to protected users

`MagicShip/Middlewares/AuthMiddleware.php`:
```php
class AuthMiddleware extends Middleware
{
    public static function handle(Request $request): Request|Response
    {
        // Return the request (success) if the user is authenticated
        if (Authentication::isLogged())
            return $request;

        // Redirect the client if not authenticated yet
        return Response::redirect('/login');
    }
}
```

Then, we need to use our middleware to protect our routes,
in this example, we shall use the group feature

`MagicShip/Routes/web.php`:
```php
Router::getInstance()->addGroup(
    ['middlewares' => AuthMiddleware::class],
    Route::view('/', 'home')
);
```

[< Back to summary](../README.md)