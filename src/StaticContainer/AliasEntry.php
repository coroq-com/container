<?php
declare(strict_types=1);
namespace Coroq\Container\StaticContainer;

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Psr\Container\ContainerInterface;

class AliasEntry implements EntryInterface {
  private string $targetId;

  public function __construct(string $targetId) {
    $this->targetId = $targetId;
  }

  public function getValue(ContainerInterface $container, ArgumentsResolverInterface $argumentsResolver) {
    return $container->get($this->targetId);
  }
}
