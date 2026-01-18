<?php
declare(strict_types=1);
namespace Coroq\Container\StaticContainer;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class ClassEntry implements EntryInterface {
  private string $className;

  public function __construct(string $className) {
    $this->className = $className;
  }

  public function has(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver): bool {
    return true;
  }

  public function getValue(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver) {
    $class = new ReflectionClass($this->className);
    $constructor = $class->getConstructor();
    $arguments = $constructor
      ? $argumentsResolver->resolve($constructor, $container)
      : [];
    return new $this->className(...$arguments);
  }
}
