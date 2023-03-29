<?php
declare(strict_types=1);
namespace Coroq\Container\Entry;

use Psr\Container\ContainerInterface;

class Alias implements EntryInterface {
  /** @var string */
  private $targetId;

  public function __construct(string $targetId) {
    $this->targetId = $targetId;
  }

  public function getValue(ContainerInterface $container) {
    return $container->get($this->targetId);
  }
}
