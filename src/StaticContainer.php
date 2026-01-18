<?php
declare(strict_types=1);
namespace Coroq\Container;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\StaticContainer\AliasEntry;
use Coroq\Container\StaticContainer\ClassEntry;
use Coroq\Container\StaticContainer\EntryInterface;
use Coroq\Container\StaticContainer\FactoryEntry;
use Coroq\Container\StaticContainer\SingletonEntry;
use Coroq\Container\StaticContainer\ValueEntry;
use Coroq\Container\Exception\NotFoundException;

class StaticContainer implements CascadingContainerInterface {
  use CircularDependencyDetectionTrait;
  use CascadingContainerTrait;

  private array $entries;
  private ?ArgumentsResolverInterface $argumentsResolver = null;

  public function __construct(?ArgumentsResolverInterface $argumentsResolver = null) {
    $this->entries = [];
    if ($argumentsResolver !== null) {
      $this->setArgumentsResolver($argumentsResolver);
    }
  }

  public function setArgumentsResolver(ArgumentsResolverInterface $argumentsResolver): void {
    $this->argumentsResolver = $argumentsResolver;
  }

  private function getArgumentsResolver(): ArgumentsResolverInterface {
    if ($this->argumentsResolver === null) {
      throw new \LogicException('ArgumentsResolver is not set. Call setArgumentsResolver() before using this container.');
    }
    return $this->argumentsResolver;
  }

  /**
   * Finds an entry of the container by its identifier and returns it.
   *
   * @param string $id Identifier of the entry to look for.
   *
   * @throws NotFoundException  No entry was found for the identifier.
   *
   * @return mixed Entry.
   */
  public function get(string $id) {
    if (!$this->has($id)) {
      throw new NotFoundException("The entry '$id' was not found in the container.");
    }
    try {
      $this->detectRecursion($id);
      $entry = $this->entries[$id];
      if ($entry instanceof EntryInterface) {
        $entry = $entry->getValue($this->getRootContainer(), $this->getArgumentsResolver());
      }
      return $entry;
    }
    finally {
      $this->clearRecursionGuard($id);
    }
  }

  /**
   * Returns true if the container can return an entry for the given identifier.
   * Returns false otherwise.
   *
   * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
   * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
   *
   * @param string $id Identifier of the entry to look for.
   *
   * @return bool
   */
  public function has(string $id): bool {
    if (!array_key_exists($id, $this->entries)) {
      return false;
    }
    try {
      $this->detectRecursion($id);
      $entry = $this->entries[$id];
      if ($entry instanceof EntryInterface) {
        return $entry->has($this->getRootContainer(), $this->getArgumentsResolver());
      }
      return true;
    }
    finally {
      $this->clearRecursionGuard($id);
    }
  }

  /**
   * Sets an entry in the container by its identifier.
   *
   * @param string $id Identifier of the entry to set.
   * @param mixed $entry The entry to set.
   *
   * @return void
   */
  public function set(string $id, $entry): void {
    $this->entries[$id] = $entry;
  }

  public function setFactory(string $id, callable $factory): void {
    $this->set($id, new FactoryEntry($factory));
  }

  public function setSingletonFactory(string $id, callable $factory): void {
    $this->set($id, new SingletonEntry(new FactoryEntry($factory)));
  }

  public function setClass(string $id, string $className): void {
    $this->set($id, new ClassEntry($className));
  }

  public function setSingletonClass(string $id, string $className): void {
    $this->set($id, new SingletonEntry(new ClassEntry($className)));
  }

  public function setValue(string $id, $value): void {
    $this->set($id, new ValueEntry($value));
  }

  public function setAlias(string $id, string $targetId): void {
    $this->set($id, new AliasEntry($targetId));
  }
}
