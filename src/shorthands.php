<?php
declare(strict_types=1);
namespace Coroq\Container;

use Coroq\Container\Entry\Alias;
use Coroq\Container\Entry\Factory;
use Coroq\Container\Entry\FactoryByClass;
use Coroq\Container\Entry\Singleton;

function factory(callable $factory): Factory {
  return new Factory(new SpreadArguments($factory));
}

function factoryByClass(string $className) {
  return new FactoryByClass($className);
}

function singleton(callable $factory): Singleton {
  return new Singleton(new Factory(new SpreadArguments($factory)));
}

function singletonByClass(string $className) {
  return new Singleton(new FactoryByClass($className));
}

function alias(string $targetId): Alias {
  return new Alias($targetId);
}

function spread(callable $callable): callable {
  return new SpreadArguments($callable);
}
