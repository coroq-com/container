<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Psr\Container\ContainerInterface;

class Singleton implements EntryInterface {
  /** @var callable */
  private $factory;
  /** @var bool */
  private $initialized;
  /** @var mixed */
  private $instance;

  public function __construct(callable $factory) {
    $this->factory = $factory;
    $this->initialized = false;
  }

  public function getValue(ContainerInterface $container) {
    if (!$this->initialized) {
      $this->instance = ($this->factory)($container);
      $this->initialized = true;
    }
    return $this->instance;
  }
}
