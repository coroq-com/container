<?php
declare(strict_types=1);
namespace Coroq\Container;

use Coroq\CallableReflector\CallableReflector;
use Psr\Container\ContainerInterface;

class SpreadArguments {
  const CONTAINER_NAME = '__container';

  /** @var callable */
  private $callable;

  /** @var array<string> */
  private $parameterNames;

  public function __construct(callable $callable) {
    $this->callable = $callable;
    $this->parameterNames = [];
    $reflection = CallableReflector::createFromCallable($callable);
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
}
