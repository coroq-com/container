# DI Container

A minimalistic, easy-to-learn Dependency Injection (DI) Container for PHP.

This library focuses on simplicity and ease of use, providing a streamlined approach to Dependency Injection without the need for a complex DSL, auto-wiring, or extensive configuration. As a result, it is beginner-friendly and well-suited for those using DI for the first time or for projects where a more elaborate solution might not be necessary.

## Features

- PSR-11 compatible (implements `Psr\Container\ContainerInterface`)
- Lightweight
- No need to learn a DSL (Domain Specific Language) - reduces learning time
- No auto-wiring or advanced features, keeping it simple and focused

## Limitations

- No auto-wiring: Services need to be manually defined and registered in the container
- No configuration file
- No built-in support for handling circular dependencies graphs

## Installation

Use [Composer](https://getcomposer.org/) to install the library:

```bash
composer require coroq/container
```

## Usage

```php
use Coroq\Container\Container;
use Coroq\Container\Entry\Factory;
use Coroq\Container\Entry\Singleton;
use Coroq\Container\Entry\Value;

// Create a container.
$container = new Container();

// Register some entries.

// Value: The provided value (an array in this case) will be returned as-is when requested.
$container->set('Config', new Value(['site_name' => 'Container Example']));

// Singleton: Only one instance of MyService will be created and reused.
$container->set('MyService', new Singleton(function($container) {
  $config = $container->get('Config');
  $myService = new MyService();
  $myService->setSiteName($config['site_name']);
  return $myService;
}));

// Factory: A new instance of MyEntity will be created every time it's requested.
$container->set('MyEntity', new Factory(function($container) {
  $myService = $container->get('MyService');
  return new MyEntity($myService);
}));

// Get a new instance of MyEntity from the container.
$myEntity = $container->get('MyEntity');
```

## License

This project is licensed under the MIT License.
