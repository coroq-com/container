<?php
declare(strict_types=1);
namespace Coroq\Container;

use Coroq\Container\Entry\EntryInterface;
use Coroq\Container\Entry\Value;
use Coroq\Container\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface {
  /** @var array<string,EntryInterface> */
  private $entries;

  public function __construct() {
    $this->entries = [];
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
      throw new NotFoundException("Entry not found: {$id}");
    }
    return $this->entries[$id]->getValue($this);
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
    return isset($this->entries[$id]);
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
    if (!($entry instanceof EntryInterface)) {
      $entry = new Value($entry);
    }
    $this->entries[$id] = $entry;
  }

  /**
   * Sets multiple entries at onece
   * @param array<string, mixed> $entries Associative array of entry identifiers and their values.
   * @return void
   */
  public function setMany(array $entries): void {
    foreach ($entries as $id => $entry) {
      $this->set($id, $entry);
    }
  }
}
