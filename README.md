# Coroq Container

A lightweight, PSR-11 compatible Dependency Injection (DI) Container for PHP.

This library provides a modular approach to dependency injection with type-based autowiring. It offers static and dynamic container implementations that can be used independently or together through the OmniContainer.

## Features

- **PSR-11 compatible**: Implements `Psr\Container\ContainerInterface`
- **Modular architecture**: Multiple container types that can be used individually or combined
- **Type-based autowiring**: Automatically resolves dependencies based on type declarations
- **Singleton management**: Built-in support for shared instances
- **Namespace-based resolution**: Automatically instantiates classes from configured namespaces
- **Circular dependency detection**: Prevents infinite recursion with clear error messages
- **Minimal dependencies**: Only requires PHP 8.0+ and PSR Container Interface

## Installation

```bash
composer require coroq/container
```

## Usage

### OmniContainer

The OmniContainer combines static and dynamic containers for a complete DI solution:

```php
use Coroq\Container\OmniContainer;

// Create container
$container = new OmniContainer();

// Configure namespaces for auto-resolution
$container->addNamespace('App\\Domain');
$container->addNamespace('App\\Infrastructure');

// Register entries
$container->setValue('config', ['db' => 'mysql:host=localhost;dbname=app']);
$container->setClass('userRepository', UserRepository::class);
$container->setSingletonClass('logger', Logger::class);

// Register factory methods
$container->setFactory('session', function($config) {
    return new Session($config['session_timeout']);
});
$container->setSingletonFactory('database', function($config) {
    return new Database($config['db']);
});

// Create alias
$container->setAlias('repos.user', 'userRepository');

// Retrieve entries
$logger = $container->get('logger');
$userRepo = $container->get('userRepository');
$userService = $container->get(App\Domain\UserService::class);
```

### StaticContainer

For explicitly registered entries:

```php
use Coroq\Container\StaticContainer;
use Coroq\Container\ArgumentsResolver\TypeBasedArgumentsResolver;

$resolver = new TypeBasedArgumentsResolver($container);
$container = new StaticContainer($resolver);

// Register entries
$container->setValue('appName', 'My App');
$container->setFactory('userService', function($userRepository) {
    return new UserService($userRepository);
});
$container->setSingletonClass('mailer', Mailer::class);

// Get entries
$service = $container->get('userService');
```

### DynamicContainer

Automatically instantiates classes from registered namespaces:

```php
use Coroq\Container\DynamicContainer;

$container = new DynamicContainer();
$container->addNamespace('App\\Domain');

// Auto-creates instances from registered namespaces
$userService = $container->get(App\Domain\UserService::class);
```

### CompositeContainer

Delegates to multiple containers:

```php
use Coroq\Container\CompositeContainer;

$container = new CompositeContainer();
$container->addContainer($staticContainer);
$container->addContainer($dynamicContainer);

// Searches containers in order until entry is found
$service = $container->get('service');
```

## License

MIT License