<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Psr\Container\ContainerInterface;

class ValueEntry implements EntryInterface {
  private $value;

  /**
   * @param mixed $value
   */
  public function __construct($value) {
    $this->value = $value;
  }

  public function getValue(ContainerInterface $container) {
    return $this->value;
  }
}
