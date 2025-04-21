<?php
declare(strict_types=1);
namespace Coroq\Container;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\Entry\AliasEntry;
use Coroq\Container\Entry\ClassEntry;
use Coroq\Container\Entry\EntryInterface;
use Coroq\Container\Entry\FactoryEntry;
use Coroq\Container\Entry\SingletonEntry;
use Coroq\Container\Entry\ValueEntry;
use Coroq\Container\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class StaticContainer implements ContainerInterface {
  use CircularDependencyDetectionTrait;

  private array $entries;
  private ArgumentsResolverInterface $argumentsResolver;

  public function __construct(ArgumentsResolverInterface $argumentsResolver) {
    $this->entries = [];
    $this->argumentsResolver = $argumentsResolver;
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
        $entry = $entry->getValue($this);
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
    return array_key_exists($id, $this->entries);
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
    $this->set($id, new FactoryEntry($this->argumentsResolver, $factory));
  }

  public function setSingletonFactory(string $id, callable $factory): void {
    $this->set($id, new SingletonEntry(new FactoryEntry($this->argumentsResolver, $factory)));
  }

  public function setClass(string $id, string $className): void {
    $this->set($id, new ClassEntry($this->argumentsResolver, $className));
  }

  public function setSingletonClass(string $id, string $className): void {
    $this->set($id, new SingletonEntry(new ClassEntry($this->argumentsResolver, $className)));
  }

  public function setValue(string $id, $value): void {
    $this->set($id, new ValueEntry($value));
  }

  public function setAlias(string $id, string $targetId): void {
    $this->set($id, new AliasEntry($targetId));
  }
}
