<?php
declare(strict_types=1);
namespace Coroq\Container\StaticContainer;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Psr\Container\ContainerInterface;

interface EntryInterface {
  /**
   * Check if this entry can be resolved without throwing NotFoundException.
   *
   * @param ContainerInterface $container
   * @param ArgumentsResolverInterface $argumentsResolver
   * @return bool
   */
  public function has(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver): bool;

  /**
   * @param ContainerInterface $container
   * @param ArgumentsResolverInterface $argumentsResolver
   * @return mixed
   */
  public function getValue(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver);
}
