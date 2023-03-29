<?php
declare(strict_types=1);
namespace Coroq\Container;

use Coroq\Container\Entry\EntryInterface;
use Coroq\Container\Exception\NotFoundException;
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
   * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
   * @throws ContainerExceptionInterface Error while retrieving the entry.
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

  public function set(string $id, EntryInterface $entry): void {
    $this->entries[$id] = $entry;
  }
}
