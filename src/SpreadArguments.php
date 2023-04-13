<?php
declare(strict_types=1);
namespace Coroq\Container;

use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

class SpreadArguments {
  const CONTAINER_NAME = '__container';

  /** @var callable */
  private $callable;

  /** @var array<string> */
  private $parameterNames;

  public function __construct(callable $callable) {
    $this->callable = $callable;
    $this->parameterNames = [];
    $reflection = $this->reflectionCallable($callable);
    foreach ($reflection->getParameters() as $parameter) {
      $this->parameterNames[] = $parameter->getName();
    }
  }

  /**
   * @return mixed
   */
  public function __invoke(ContainerInterface $container) {
    $arguments = [];
    foreach ($this->parameterNames as $parameterName) {
      if ($parameterName == self::CONTAINER_NAME) {
        $arguments[] = $container;
      }
      else {
        $arguments[] = $container->get($parameterName);
      }
    }
    return call_user_func_array($this->callable, $arguments);
  }

  /**
   * Get a reflection of a callable
   * @param callable $callable
   * @return ReflectionFunctionAbstract
   */
  private function reflectionCallable(callable $callable): ReflectionFunctionAbstract {
    if (is_array($callable)) {
      return new ReflectionMethod($callable[0], $callable[1]);
    }
    if ($callable instanceof Closure) {
      return new ReflectionFunction($callable);
    }
    if (is_object($callable)) {
      return new ReflectionMethod($callable, '__invoke');
    }
    if (is_string($callable)) {
      if (strpos($callable, '::') === false) {
        return new ReflectionFunction($callable);
      }
      return new ReflectionMethod($callable);
    }
    // @codeCoverageIgnoreStart
    throw new InvalidArgumentException('Unknown type of callable. ' . gettype($callable));
    // @codeCoverageIgnoreEnd
  }
}
