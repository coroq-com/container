<?php
declare(strict_types=1);

namespace Coroq\Container;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\ArgumentsResolver\TypeBasedArgumentsResolver;
use Coroq\Container\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class DynamicContainer implements ContainerInterface {
  use CircularDependencyDetectionTrait;

  /** @var string[] */
  private array $namespaces;

  /** @var object[] */
  private array $items;

  private ArgumentsResolverInterface $argumentsResolver;

  public function __construct(?ArgumentsResolverInterface $argumentsResolver = null) {
    $this->namespaces = [];
    $this->items = [];
    $this->argumentsResolver = $argumentsResolver ?: new TypeBasedArgumentsResolver($this);
  }

  public function addNamespace(string $namespace): void {
    if (substr($namespace, -1) !== '\\') {
      $namespace .= '\\';
    }
    $this->namespaces[] = $namespace;
  }

  public function get(string $id) {
    $className = $id;
    if (!$this->has($className)) {
      throw new NotFoundException("The entry '$id' was not found in the container.");
    }
    if (isset($this->items[$className])) {
      return $this->items[$className];
    }
    try {
      $this->detectRecursion($className);
      $arguments = $this->argumentsResolver->resolveConstructorArguments($className);
      $this->items[$className] = new $className(...$arguments);
      return $this->items[$className];
    }
    finally {
      $this->clearRecursionGuard($className);
    }
  }

  public function has(string $id): bool {
    $className = $id;
    if (isset($this->items[$className])) {
      return true;
    }
    if (!$this->matchNamespace($className)) {
      return false;
    }
    return class_exists($className);
  }

  private function matchNamespace(string $className): bool {
    foreach ($this->namespaces as $namespace) {
      if (substr($className, 0, strlen($namespace)) === $namespace) {
        return true;
      }
    }
    return false;
  }
}
