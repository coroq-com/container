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
use function Coroq\Container\value;

// Create a container.
$container = new Container();

// Register some entries.

// Value: The provided value (an array in this case) will be returned as-is when requested.
$container->set('config', value(['site_name' => 'Container Example']));

// Singleton: Only one instance of MyService will be created and reused.
$container->set('myService', singleton(function($container) {
  $config = $container->get('config');
  $myService = new MyService();
  $myService->setSiteName($config['site_name']);
  return $myService;
}));

// Factory: A new instance of MyEntity will be created every time it's requested.
$container->set('myEntity', factory(function($container) {
  $myService = $container->get('myService');
  return new MyEntity($myService);
}));

// Get a new instance of MyEntity from the container.
$myEntity = $container->get('myEntity');
```

### Using spread() for concise and IDE-friendly code

If you want to take advantage of your IDE's type hinting feature, you can use the spread() function. This function automatically assigns arguments for the singleton() and factory() functions based on their parameter names.

```php
use function Coroq\Container\spread;

// $config is taken from $container->get('config').
$container->set('myService', singleton(spread(function(array $config) {
  $myService = new MyService();
  $myService->setSiteName($config['site_name']);
  return $myService;
})));

// $myService is taken from $container->get('myService').
$container->set('myEntity', factory(spread(function(MyService $myService) {
  return new MyEntity($myService);
})));
```

## License

This project is licensed under the MIT License.
