# DI Container

A minimalistic, easy-to-learn Dependency Injection (DI) Container for PHP.

This library focuses on simplicity and ease of use, providing a streamlined approach to Dependency Injection without the need for a complex DSL, auto-wiring, or extensive configuration. As a result, it is beginner-friendly and well-suited for those using DI for the first time or for projects where a more elaborate solution might not be necessary.

## Features

- **PSR-11 compatible**: implements `Psr\Container\ContainerInterface`
- **Lightweight**: No bloat or unnecessary features
- **Simplicity**: No DSL to learn, reducing learning time
- **Focused**: No auto-wiring or advanced features, keeping the library lean and efficient

## Limitations

- **No auto-wiring**: Services need to be manually defined and registered in the container
- **No configuration file**: All configuration is done programmatically
- **No built-in support for circular dependencies**: Circular dependency graphs must be handled manually

## Installation

Use [Composer](https://getcomposer.org/) to install the library:

```bash
composer require coroq/container
```

## Usage

### Basic usage

```php
use Coroq\Container\Container;
use function Coroq\Container\factory;
use function Coroq\Container\singleton;

// Create a container.
$container = new Container();

// Register some entries.

// A regular value.
$container->set('config', ['site_name' => 'Container Example']);

// Singleton: Only one instance of MyService is created and reused.
$container->set('myService', singleton(function($config) {
  // singleton() provides arguments from container entries that match the parameter name.
  // So, $config is retrieved from $container->get('config').
  $myService = new MyService();
  $myService->setSiteName($config['site_name']);
  return $myService;
}));

// Factory: A new instance of MyEntity is created every time it's requested.
$container->set('myEntity', factory(function(MyService $myService) {
  // You can use type hints for the arguments.
  // Note that arguments are bound by their names, not their types.
  return new MyEntity($myService);
}));

// Get a new instance of MyEntity from the container.
$myEntity = $container->get('myEntity');
```

### Setting multiple entries at once

```php
// With the setMany() method, you can quickly add multiple entries to the container.
$container->setMany([
  'entry1' => 'value1',
  'entry2' => 'value2',
]);

// This is the same as doing:
$container->set('entry1', 'value1');
$container->set('entry2', 'value2');
```

### Alias

Create aliases for a single entry using the alias() function. This allows you to reference the same entry in the container with different identifiers.

```php
use function Coroq\Container\alias;

$container->setMany([
  'psr17Factory' => singleton(function() {
    return new Psr17Factory();
  }),
  'requestFactory' => alias('psr17Factory'),
  'responseFactory' => alias('psr17Factory'),
  'uriFactory' => alias('psr17Factory'),
]);

$requestFactory = $container->get('requestFactory');
// $requestFactory === $container->get('psr17Factory').
```

## License

This project is licensed under the MIT License.
