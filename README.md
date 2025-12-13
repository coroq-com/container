# Coroq Container

A lightweight, PSR-11 compatible Dependency Injection Container for PHP.

## Installation

```bash
composer require coroq/container
```

## Quick Start

```php
use Coroq\Container\OmniContainer;

$container = new OmniContainer();

// Register a value
$container->setValue('app.name', 'My Application');
$container->get('app.name');  // => 'My Application'

// Register a class (new instance each time)
$container->setClass('userRepository', UserRepository::class);
$container->get('userRepository');  // => new UserRepository()
$container->get('userRepository');  // => new UserRepository() (different instance)

// Register a singleton class (same instance every time)
$container->setSingletonClass(Logger::class, Logger::class);
$container->get(Logger::class);  // => new Logger()
$container->get(Logger::class);  // => same Logger instance

// Register a factory with auto-resolved arguments
// By default, arguments are resolved by type (TypeBasedArgumentsResolver)
$container->setFactory('pdo', function(Logger $logger) {
    $logger->info('Creating PDO connection');
    return new PDO('mysql:host=localhost;dbname=app');
});
$container->get('pdo');  // => Logger is auto-injected, returns new PDO()

// Register a singleton factory
$container->setSingletonFactory('database', function(PDO $pdo, Logger $logger) {
    return new Database($pdo, $logger);
});
$container->get('database');  // => new Database() with PDO and Logger injected
$container->get('database');  // => same Database instance

// Create an alias
$container->setAlias('db', 'database');
$container->get('db');  // => same as $container->get('database')

// Auto-resolve classes from registered namespaces
$container->addNamespace('App\\Domain');
$container->get(App\Domain\UserService::class);  // => auto-instantiated with dependencies
```

## Features

- PSR-11 compatible (`Psr\Container\ContainerInterface`)
- Type-based or name-based autowiring
- Singleton support for classes and factories
- Namespace-based auto-resolution
- Circular dependency detection

## Arguments Resolution

The container automatically resolves constructor and factory arguments.

### Type-Based Resolution (Default)

Resolves arguments by their type declarations:

```php
use Coroq\Container\OmniContainer;

// TypeBasedArgumentsResolver is used by default
$container = new OmniContainer();

$container->setSingletonClass(Logger::class, Logger::class);
$container->setSingletonClass(UserRepository::class, UserRepository::class);

// UserService constructor: __construct(Logger $logger, UserRepository $repo)
// Both arguments are resolved by their class types
$container->setClass(UserService::class, UserService::class);
```

### Name-Based Resolution

Resolves arguments by their parameter names. Useful for configuration-driven projects:

```php
use Coroq\Container\OmniContainer;
use Coroq\Container\ArgumentsResolver\NameBasedArgumentsResolver;

$container = new OmniContainer(new NameBasedArgumentsResolver());

// Register entries - names must match parameter names exactly
$container->setValue('dsn', 'mysql:host=localhost;dbname=app');
$container->setValue('timeout', 30);
$container->setValue('logger', new Logger());

// Factory parameters $dsn, $timeout are resolved by name
$container->setSingletonFactory('database', function($dsn, $timeout) {
    return new Database($dsn, $timeout);
});

// Constructor parameter $logger is resolved by name
// class UserService { public function __construct($logger) { ... } }
$container->setClass('userService', UserService::class);
```

## Advanced: Building Custom Container Hierarchies

For most use cases, `OmniContainer` is sufficient. This section is for users who need fine-grained control over container composition.

### Container Types

**StaticContainer** - For explicitly registered entries:

```php
use Coroq\Container\StaticContainer;
use Coroq\Container\ArgumentsResolver\TypeBasedArgumentsResolver;

$container = new StaticContainer();
$container->setArgumentsResolver(new TypeBasedArgumentsResolver());

$container->setValue('appName', 'My App');
$container->setFactory('service', function() { return new Service(); });
```

**DynamicContainer** - Auto-instantiates classes from registered namespaces:

```php
use Coroq\Container\DynamicContainer;
use Coroq\Container\ArgumentsResolver\TypeBasedArgumentsResolver;

$container = new DynamicContainer();
$container->setArgumentsResolver(new TypeBasedArgumentsResolver());
$container->addNamespace('App\\Domain');

// Automatically creates App\Domain\UserService
$service = $container->get(App\Domain\UserService::class);
```

**CompositeContainer** - Delegates to multiple containers in order:

```php
use Coroq\Container\CompositeContainer;

$composite = new CompositeContainer();
$composite->addContainer($staticContainer);   // checked first
$composite->addContainer($dynamicContainer);  // checked second

$service = $composite->get('service');  // searches in order
```

### Cascading Containers

When containers are nested, child containers may need to resolve dependencies registered in sibling or parent containers. The `setRootContainer()` method enables this:

```php
use Coroq\Container\StaticContainer;
use Coroq\Container\DynamicContainer;
use Coroq\Container\CompositeContainer;
use Coroq\Container\ArgumentsResolver\TypeBasedArgumentsResolver;

$resolver = new TypeBasedArgumentsResolver();

// Static container holds explicitly registered entries
$static = new StaticContainer();
$static->setArgumentsResolver($resolver);
$static->setSingletonClass(Logger::class, Logger::class);

// Dynamic container auto-instantiates from namespace
$dynamic = new DynamicContainer();
$dynamic->setArgumentsResolver($resolver);
$dynamic->addNamespace('App\\Domain');

// Composite container combines both
$root = new CompositeContainer();
$root->addContainer($static);
$root->addContainer($dynamic);

// Enable cascading: child containers resolve dependencies via root
$root->setRootContainer($root);

// Now DynamicContainer can resolve Logger (registered in StaticContainer)
// when instantiating App\Domain\UserService
$service = $root->get(App\Domain\UserService::class);
```

`OmniContainer` handles cascading automatically.

## License

MIT License
