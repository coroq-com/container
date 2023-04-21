<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Psr\Container\ContainerInterface;
use ReflectionClass;

class FactoryByClass implements EntryInterface {
  /** @vaar ReflectionClass */
  private $classReflection;

  /** @var array */
  private $parameterNames;

  public function __construct(string $className) {
    $this->classReflection = new ReflectionClass($className);
    $constructorReflection = $this->classReflection->getConstructor();
    $this->parameterNames = [];
    if ($constructorReflection) {
      foreach ($constructorReflection->getParameters() as $parameter) {
        $this->parameterNames[] = $parameter->getName();
      }
    }
  }

  public function getValue(ContainerInterface $container) {
    $arguments = [];
    foreach ($this->parameterNames as $parameterName) {
      $arguments[] = $container->get($parameterName);
    }
    return $this->classReflection->newInstanceArgs($arguments);
  }
}
