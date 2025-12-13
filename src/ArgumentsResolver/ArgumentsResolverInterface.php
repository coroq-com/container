<?php
declare(strict_types=1);
namespace Coroq\Container\ArgumentsResolver;

use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;

interface ArgumentsResolverInterface {
  /**
   * Resolve arguments for a callable or constructor.
   *
   * @param ReflectionFunctionAbstract $reflection
   * @param ContainerInterface $container
   * @return array<mixed> Resolved arguments
   */
  public function resolve(ReflectionFunctionAbstract $reflection, ContainerInterface $container): array;
}
