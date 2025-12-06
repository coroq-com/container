<?php
declare(strict_types=1);
namespace Coroq\Container\ArgumentsResolver;

use Coroq\CallableReflector\CallableReflector;
use Coroq\Container\Exception\AutowiringException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionParameter;

class NameBasedArgumentsResolver implements ArgumentsResolverInterface {
  private ?ContainerInterface $container = null;

  public function setContainer(ContainerInterface $container): void {
    $this->container = $container;
  }

  private function getContainer(): ContainerInterface {
    if ($this->container === null) {
      throw new \LogicException('Container is not set. Call setContainer() before using this resolver.');
    }
    return $this->container;
  }

  public function resolveConstructorArguments(string $className): array {
    $class = new ReflectionClass($className);
    $constructor = $class->getConstructor();
    if ($constructor === null) {
      return [];
    }
    return $this->resolveArguments($constructor);
  }

  public function resolveCallableArguments(callable $callable): array {
    $reflection = CallableReflector::createFromCallable($callable);
    return $this->resolveArguments($reflection);
  }

  private function resolveArguments(ReflectionFunctionAbstract $reflection): array {
    $arguments = [];
    foreach ($reflection->getParameters() as $parameter) {
      if ($parameter->isVariadic()) {
        throw new AutowiringException(sprintf(
          'Variadic parameter $%s is not supported for autowiring.',
          $parameter->getName()
        ));
      }
      $arguments[] = $this->resolveArgument($parameter);
    }
    return $arguments;
  }

  private function resolveArgument(ReflectionParameter $parameter) {
    $name = $parameter->getName();
    $container = $this->getContainer();

    if (!$container->has($name) && $parameter->isDefaultValueAvailable()) {
      return $parameter->getDefaultValue();
    }

    return $container->get($name);
  }
}
