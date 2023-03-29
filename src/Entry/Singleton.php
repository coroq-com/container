<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Closure;
use Psr\Container\ContainerInterface;

class Singleton implements EntryInterface {
  /** @var Closure */
  private $factory;
  /** @var bool */
  private $initialized;
  /** @var mixed */
  private $instance;

  public function __construct(Closure $factory) {
    $this->factory = $factory;
    $this->initialized = false;
  }

  public function getValue(ContainerInterface $container) {
    if (!$this->initialized) {
      $this->instance = $this->factory->__invoke($container);
      $this->initialized = true;
    }
    return $this->instance;
  }
}
