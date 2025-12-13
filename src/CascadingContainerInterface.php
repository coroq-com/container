<?php
declare(strict_types=1);
namespace Coroq\Container;

use Psr\Container\ContainerInterface;

interface CascadingContainerInterface extends ContainerInterface {
  /**
   * Set the root container for dependency resolution.
   *
   * @param ContainerInterface $rootContainer
   * @return void
   */
  public function setRootContainer(ContainerInterface $rootContainer): void;
}
