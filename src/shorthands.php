<?php
declare(strict_types=1);
namespace Coroq\Container;

use Coroq\Container\Entry\Alias;
use Coroq\Container\Entry\Factory;
use Coroq\Container\Entry\Singleton;
use Coroq\Container\Entry\Value;

function factory(callable $factory): Factory {
  return new Factory(new SpreadArguments($factory));
}

function singleton(callable $factory): Singleton {
  return new Singleton(new SpreadArguments($factory));
}

/**
 * @param mixed $value
 */
function value($value): Value {
  return new Value($value);
}

function alias(string $targetId): Alias {
  return new Alias($targetId);
}

function spread(callable $callable): callable {
  return new SpreadArguments($callable);
}
