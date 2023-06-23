<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionParameter;

class FactoryByClass implements EntryInterface {
  /** @vaar ReflectionClass */
  private $classReflection;

  /** @var array<ReflectionParameter> */
  private $parameters;

  public function __construct(string $className) {
    $this->classReflection = new ReflectionClass($className);
    $constructorReflection = $this->classReflection->getConstructor();
    $this->parameters = [];
    if ($constructorReflection) {
      $this->parameters = $constructorReflection->getParameters();
    }
  }

  public function getValue(ContainerInterface $container) {
    $arguments = [];
    foreach ($this->parameters as $parameter) {
      $parameterName = $parameter->getName();
      try {
        $arguments[] = $container->get($parameterName);
      }
      catch (NotFoundExceptionInterface $exception) {
        if (!$parameter->isDefaultValueAvailable()) {
          throw $exception;
        }
        $arguments[] = $parameter->getDefaultValue();
      }
    }
    return $this->classReflection->newInstanceArgs($arguments);
  }
}
