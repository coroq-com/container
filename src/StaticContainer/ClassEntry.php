<?php
declare(strict_types=1);
namespace Coroq\Container\StaticContainer;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\Exception\InstantiationException;
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
    if (!class_exists($this->className) && !interface_exists($this->className)) {
      throw new InstantiationException("Cannot instantiate: class '{$this->className}' does not exist");
    }
    $class = new ReflectionClass($this->className);
    if (!$class->isInstantiable()) {
      throw new InstantiationException("Cannot instantiate: class '{$this->className}' is not instantiable");
    }
    $constructor = $class->getConstructor();
    $arguments = $constructor
      ? $argumentsResolver->resolve($constructor, $container)
      : [];
    return new $this->className(...$arguments);
  }
}
