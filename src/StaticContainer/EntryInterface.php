<?php
declare(strict_types=1);
namespace Coroq\Container\StaticContainer;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Psr\Container\ContainerInterface;

interface EntryInterface {
  /**
   * @param ContainerInterface $container
   * @param ArgumentsResolverInterface $argumentsResolver
   * @return mixed
   */
  public function getValue(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver);
}
