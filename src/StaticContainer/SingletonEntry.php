<?php
declare(strict_types=1);
namespace Coroq\Container\StaticContainer;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Psr\Container\ContainerInterface;

class SingletonEntry implements EntryInterface {
  private EntryInterface $entry;
  private bool $cached;
  private $cache;

  public function __construct(EntryInterface $entry) {
    $this->entry = $entry;
    $this->cached = false;
  }

  public function has(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver): bool {
    return $this->entry->has($container, $argumentsResolver);
  }

  public function getValue(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver) {
    if (!$this->cached) {
      $this->cache = $this->entry->getValue($container, $argumentsResolver);
      $this->cached = true;
    }
    return $this->cache;
  }
}
