<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Closure;
use Psr\Container\ContainerInterface;

class Factory implements EntryInterface {
  /** @var Closure */
  private $factory;

  public function __construct(Closure $factory) {
    $this->factory = $factory;
  }

  public function getValue(ContainerInterface $container) {
    return $this->factory->__invoke($container);
  }
}
