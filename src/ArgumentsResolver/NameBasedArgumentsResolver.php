<?php
declare(strict_types=1);
namespace Coroq\Container\ArgumentsResolver;

use Coroq\Container\Exception\AutowiringException;
use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;
use ReflectionParameter;

class NameBasedArgumentsResolver implements ArgumentsResolverInterface {

  public function resolve(ReflectionFunctionAbstract $reflection, ContainerInterface $container): array {
    $arguments = [];
    foreach ($reflection->getParameters() as $parameter) {
      if ($parameter->isVariadic()) {
        throw new AutowiringException(sprintf(
          'Variadic parameter $%s is not supported for autowiring.',
          $parameter->getName()
        ));
      }
      $arguments[] = $this->resolveArgument($parameter, $container);
    }
    return $arguments;
  }

  private function resolveArgument(ReflectionParameter $parameter, ContainerInterface $container) {
    $name = $parameter->getName();

    if (!$container->has($name) && $parameter->isDefaultValueAvailable()) {
      return $parameter->getDefaultValue();
    }

    return $container->get($name);
  }
}
