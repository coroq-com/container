<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Psr\Container\ContainerInterface;

class ClassEntry implements EntryInterface {
  private ArgumentsResolverInterface $argumentsResolver;
  private string $className;

  public function __construct(ArgumentsResolverInterface $argumentsResolver, string $className) {
    $this->argumentsResolver = $argumentsResolver;
    $this->className = $className;
  }

  public function getValue(ContainerInterface $container) {
    $arguments = $this->argumentsResolver->resolveConstructorArguments($this->className);
    $className = $this->className;
    return new $className(...$arguments);
  }
}
