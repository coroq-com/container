<?php
declare(strict_types=1);
namespace Coroq\Container\ArgumentsResolver;

use Coroq\Container\Exception\AutowiringException;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class TypeBasedArgumentsResolver implements ArgumentsResolverInterface {

  public function resolve(ReflectionFunctionAbstract $reflection, ContainerInterface $container): array {
    $arguments = [];
    foreach ($reflection->getParameters() as $parameter) {
      $arguments[] = $this->resolveArgument($reflection, $parameter, $container);
    }
    return $arguments;
  }

  private function resolveArgument(
    ReflectionFunctionAbstract $reflection,
    ReflectionParameter $parameter,
    ContainerInterface $container
  ) {
    try {
      $parameterType = $parameter->getType();

      // No type declaration
      if ($parameterType === null) {
        throw new AutowiringException(sprintf(
          'The parameter $%s in %s lacks a type declaration and cannot be auto-wired.',
          $parameter->getName(),
          self::describeCallable($reflection)
        ));
      }

      // Complex type declaration
      if (!($parameterType instanceof ReflectionNamedType)) {
        throw new AutowiringException(sprintf(
          'The parameter $%s in %s is not a simple named type. It has a complex type declaration (union or intersection), which cannot be auto-wired.',
          $parameter->getName(),
          self::describeCallable($reflection)
        ));
      }

      // Built-in types
      if ($parameterType->isBuiltin()) {
        throw new AutowiringException(sprintf(
          'The parameter $%s in %s is of a built-in type, which cannot be auto-wired.',
          $parameter->getName(),
          self::describeCallable($reflection)
        ));
      }

      return $container->get($parameterType->getName());
    }
    catch (ContainerExceptionInterface $exception) {
      if ($exception instanceof AutowiringException || $exception instanceof NotFoundExceptionInterface) {
        if ($parameter->isDefaultValueAvailable()) {
          return $parameter->getDefaultValue();
        }
      }
      throw $exception;
    }
  }

  private static function describeCallable(ReflectionFunctionAbstract $callableReflection): string {
    if ($callableReflection->isClosure()) {
      return sprintf(
        'the closure defined in the file %s line %d',
        $callableReflection->getFileName(),
        $callableReflection->getStartLine()
      );
    }
    if ($callableReflection instanceof ReflectionFunction) {
      return $callableReflection->getName();
    }
    if ($callableReflection instanceof ReflectionMethod) {
      return sprintf(
        '%s::%s',
        $callableReflection->getDeclaringClass()->getName(),
        $callableReflection->getName()
      );
    }
    // @codeCoverageIgnoreStart
    throw new LogicException('Unknown type of ReflectionFunctionAbstract: ' . get_class($callableReflection));
    // @codeCoverageIgnoreEnd
  }
}
