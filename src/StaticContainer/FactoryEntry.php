<?php
declare(strict_types=1);
namespace Coroq\Container\StaticContainer;

use Coroq\CallableReflector\CallableReflector;
use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Psr\Container\ContainerInterface;

class FactoryEntry implements EntryInterface {
  private $factory;

  public function __construct(callable $factory) {
    $this->factory = $factory;
  }

  public function has(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver): bool {
    return true;
  }

  public function getValue(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver) {
    $reflection = CallableReflector::createFromCallable($this->factory);
    $arguments = $argumentsResolver->resolve($reflection, $container);
    return ($this->factory)(...$arguments);
  }
}
