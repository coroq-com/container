<?php
declare(strict_types=1);
namespace Coroq\Container\ArgumentsResolver;

use Psr\Container\ContainerInterface;

interface ArgumentsResolverInterface {

  /**
   * @param ContainerInterface $container
   * @return void
   */
  public function setContainer(ContainerInterface $container): void;

  /**
   * @param string $className
   * @return array<mixed> Resolved arguments
   */
  public function resolveConstructorArguments(string $className): array;

  /**
   * @param callable $callable
   * @return array<mixed> Resolved arguments
   */
  public function resolveCallableArguments(callable $callable): array;
}
