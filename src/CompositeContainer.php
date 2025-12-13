<?php
declare(strict_types=1);
namespace Coroq\Container;

use Coroq\Container\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class CompositeContainer implements CascadingContainerInterface {
  /** @var ContainerInterface[] */
  private array $containers;

  public function __construct() {
    $this->containers = [];
  }

  public function addContainer(ContainerInterface $container): void {
    $this->containers[] = $container;
  }

  public function setRootContainer(ContainerInterface $rootContainer): void {
    foreach ($this->containers as $container) {
      if ($container instanceof CascadingContainerInterface) {
        $container->setRootContainer($rootContainer);
      }
    }
  }

  public function get(string $id) {
    foreach ($this->containers as $container) {
      if ($container->has($id)) {
        return $container->get($id);
      }
    }
    throw new NotFoundException("The entry '$id' was not found in the container.");
  }

  public function has(string $id): bool {
    foreach ($this->containers as $container) {
      if ($container->has($id)) {
        return true;
      }
    }
    return false;
  }
}
