<?php
declare(strict_types=1);
namespace Coroq\Container;

use Coroq\Container\ArgumentsResolver\TypeBasedArgumentsResolver;
use Psr\Container\ContainerInterface;

class OmniContainer implements ContainerInterface {
  use CircularDependencyDetectionTrait;
  
  private CompositeContainer $compositeContainer;
  private StaticContainer $staticContainer;
  private DynamicContainer $dynamicContainer;
  private array $aliases;

  public function __construct() {
    $this->aliases = [];

    $this->compositeContainer = new CompositeContainer();

    $argumentsResolver = new TypeBasedArgumentsResolver($this);

    $this->staticContainer = new StaticContainer($argumentsResolver);
    $this->dynamicContainer = new DynamicContainer($argumentsResolver);

    $this->compositeContainer->addContainer($this->staticContainer);
    $this->compositeContainer->addContainer($this->dynamicContainer);
  }

  public function addNamespace(string $namespace): void {
    $this->dynamicContainer->addNamespace($namespace);
  }

  public function setFactory(string $id, callable $factory): void {
    $this->staticContainer->setFactory($id, $factory);
  }

  public function setSingletonFactory(string $id, callable $factory): void {
    $this->staticContainer->setSingletonFactory($id, $factory);
  }

  public function setClass(string $id, string $className): void {
    $this->staticContainer->setClass($id, $className);
  }

  public function setSingletonClass(string $id, string $className): void {
    $this->staticContainer->setSingletonClass($id, $className);
  }

  public function setValue(string $id, $value): void {
    $this->staticContainer->setValue($id, $value);
  }

  public function setAlias(string $id, string $targetId): void {
    $this->aliases[$id] = $targetId;
  }

  public function get(string $id) {
    if (isset($this->aliases[$id])) {
      try {
        $this->detectRecursion($id);
        return $this->get($this->aliases[$id]);
      }
      finally {
        $this->clearRecursionGuard($id);
      }
    }
    return $this->compositeContainer->get($id);
  }

  public function has(string $id): bool {
    if (isset($this->aliases[$id])) {
      try {
        $this->detectRecursion($id);
        return $this->has($this->aliases[$id]);
      }
      finally {
        $this->clearRecursionGuard($id);
      }
    }
    return $this->compositeContainer->has($id);
  }
}