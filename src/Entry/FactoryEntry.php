<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Psr\Container\ContainerInterface;

class FactoryEntry implements EntryInterface {
  private ArgumentsResolverInterface $argumentsResolver;
  private $factory;

  public function __construct(ArgumentsResolverInterface $argumentsResolver, callable $factory) {
    $this->argumentsResolver = $argumentsResolver;
    $this->factory = $factory;
  }

  public function getValue(ContainerInterface $container) {
    $arguments = $this->argumentsResolver->resolveCallableArguments($this->factory);
    return ($this->factory)(...$arguments);
  }
}
