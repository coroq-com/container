<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Psr\Container\ContainerInterface;

interface EntryInterface {
  /**
   * @return mixed
   */
  public function getValue(ContainerInterface $container);
}
