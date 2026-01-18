<?php
declare(strict_types=1);
namespace Coroq\Container\StaticContainer;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Psr\Container\ContainerInterface;

class ValueEntry implements EntryInterface {
  private $value;

  /**
   * @param mixed $value
   */
  public function __construct($value) {
    $this->value = $value;
  }

  public function has(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver): bool {
    return true;
  }

  public function getValue(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver) {
    return $this->value;
  }
}
