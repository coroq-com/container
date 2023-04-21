<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Psr\Container\ContainerInterface;

class Singleton implements EntryInterface {
  /** @var EntryInterface */
  private $entry;
  /** @var bool */
  private $cached;
  /** @var mixed */
  private $cache;

  public function __construct(EntryInterface $entry) {
    $this->entry = $entry;
    $this->cached = false;
  }

  public function getValue(ContainerInterface $container) {
    if (!$this->cached) {
      $this->cache = $this->entry->getValue($container);
      $this->cached = true;
    }
    return $this->cache;
  }
}
