# Slim Route Registry

A PHP library that provides attribute-based route registration for Slim Framework applications. This library allows you
to define routes using PHP 8 attributes on your handler classes and methods, making your code more declarative and
organized.

## Requirements

- PHP 8.2 or higher
- Slim Framework 4.14 or higher
- composer/class-map-generator 1.6 or higher

## Installation

You can install the library via Composer:

```bash
composer require blackbonjour/slim-route-registry
```

## Basic Usage

### 1. Create a Route Handler

Create a class that will handle your route and add the `Route` attribute to it:

```php
<?php

namespace App\Handlers;

use BlackBonjour\SlimRouteRegistry\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Route('GET', '/hello')]
class HelloHandler
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write('Hello, World!');

        return $response;
    }
}
```

### 2. Register Routes with the RouteRegistry

In your application bootstrap file:

```php
<?php

use BlackBonjour\SlimRouteRegistry\RouteRegistry;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

// Create Slim App
$app = AppFactory::create();

// Create RouteRegistry with paths to scan for route handlers
// By default, it uses ComposerClassProvider to find classes in the specified paths
$routeRegistry = new RouteRegistry([__DIR__ . '/src/Handlers']);

// Register all routes from the specified paths
$routeRegistry->register($app);

// Run the app
$app->run();
```

## Advanced Usage

### Named Routes

You can assign names to your routes for easier URL generation:

```php
#[Route('GET', '/user/{id}', 'user-profile')]
class UserProfileHandler
{
    // ...
}
```

### Multiple HTTP Methods

You can specify multiple HTTP methods for a single route:

```php
#[Route(['GET', 'POST'], '/form')]
class FormHandler
{
    // ...
}
```

### Method-Level Routes

You can also define routes on public methods:

```php
class UserHandler
{
    #[Route('GET', '/users')]
    public function listUsers(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // List users
        return $response;
    }

    #[Route('GET', '/users/{id}')]
    public function getUser(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // Get specific user
        return $response;
    }
}
```

### Route Arguments

You can pass additional arguments to your routes:

```php
#[Route('GET', '/api/users', 'users-list', arguments: ['version' => 'v1', 'cache' => true])]
class UsersListHandler
{
    // ...
}
```

These arguments can be used to configure the route or provide additional data to the handler.

### Middleware

You can attach middleware to your routes:

```php
#[Route('GET', '/secure', 'secure-area', middlewares: [AuthMiddleware::class])]
class SecureAreaHandler
{
    // ...
}
```

### Redirects

You can define redirects using the `Redirect` attribute:

```php
#[Redirect('/old-path', '/new-path')]
#[Route('GET', '/new-path')]
class NewPathHandler
{
    // ...
}
```

You can also specify the HTTP status code for the redirect (defaults to 302):

```php
#[Redirect('/legacy', '/modern', 301)]
```

When used with a `Route` attribute, you can omit the destination path to redirect to the route's path:

```php
#[Redirect('/old-path')]
#[Route('GET', '/new-path')]
class NewPathHandler
{
    // ...
}
```

## API Reference

### RouteRegistry

The main class responsible for registering routes.

```php
public function __construct(array $paths, ClassProviderInterface $classProvider = new ComposerClassProvider())
```

- `$paths`: Array of directory paths to scan for route handler classes
- `$classProvider`: Instance of `ClassProviderInterface` (defaults to `ComposerClassProvider`)

```php
public function register(App $app): void
```

- `$app`: Slim Framework App instance

### ClassProviderInterface

An interface for classes that provide a list of class names from a given path.

```php
public function provideClasses(string $path): array
```

- `$path`: Directory path to scan for classes
- Returns: Array of fully qualified class names

### ComposerClassProvider

The default implementation of `ClassProviderInterface` that uses Composer's ClassMapGenerator to find classes.

```php
public function __construct(ClassMapGenerator $classMapGenerator = new ClassMapGenerator())
```

- `$classMapGenerator`: Optional custom instance of Composer's ClassMapGenerator (defaults to a new instance)

```php
public function provideClasses(string $path): array
```

- `$path`: Directory path to scan for classes
- Returns: Array of fully qualified class names found in the path
- Throws: `DirectoryNotFoundExceptionClass` if the path is not a valid directory
- Throws: `ClassMapGeneratorExceptionClass` if the ClassMapGenerator encounters an error

### Route Attribute

```php
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
public function __construct(
    array|string $methods,
    string $path,
    ?string $name = null,
    array $arguments = [],
    array $middlewares = [],
    array $redirects = []
)
```

- `$methods`: HTTP method(s) as string or array of strings (e.g., 'GET', ['GET', 'POST'])
- `$path`: URL path pattern (e.g., '/users/{id}')
- `$name`: Optional route name for URL generation
- `$arguments`: Array of key-value pairs to be passed to the route
- `$middlewares`: Array of middleware classes, callables, or string class names
- `$redirects`: Array of Redirect objects

### Redirect Attribute

```php
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
public function __construct(
    string $from,
    ?string $to = null,
    int $status = 302
)
```

- `$from`: Source path to redirect from
- `$to`: Destination path (can be null if used with a Route attribute)
- `$status`: HTTP status code for the redirect (defaults to 302)

## License

This library is licensed under the MIT License. See the LICENSE file for details.
