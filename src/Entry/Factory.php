<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Psr\Container\ContainerInterface;

class Factory implements EntryInterface {
  /** @var callable */
  private $factory;

  public function __construct(callable $factory) {
    $this->factory = $factory;
  }

  public function getValue(ContainerInterface $container) {
    return ($this->factory)($container);
  }
}
