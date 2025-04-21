<?php
declare(strict_types=1);

namespace Coroq\Container;

use Coroq\Container\Exception\CircularDependencyException;

trait CircularDependencyDetectionTrait {
  private array $currentlyGettingIds = [];

  protected function detectRecursion(string $id): void {
    if (isset($this->currentlyGettingIds[$id])) {
      throw new CircularDependencyException("Circular dependency detected for {$id}.");
    }
    $this->currentlyGettingIds[$id] = true;
  }

  protected function clearRecursionGuard(string $id): void {
    unset($this->currentlyGettingIds[$id]);
  }
}
