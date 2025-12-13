<?php
declare(strict_types=1);
namespace Coroq\Container;

use Psr\Container\ContainerInterface;

trait CascadingContainerTrait {
  private ?ContainerInterface $rootContainer = null;

  public function setRootContainer(ContainerInterface $rootContainer): void {
    $this->rootContainer = $rootContainer;
  }

  protected function getRootContainer(): ContainerInterface {
    return $this->rootContainer ?? $this;
  }
}
