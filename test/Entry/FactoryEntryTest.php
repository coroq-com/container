<?php
declare(strict_types=1);

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\StaticContainer\FactoryEntry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Coroq\CallableReflector\CallableReflector;

/**
 * @covers Coroq\Container\StaticContainer\FactoryEntry
 */
class FactoryEntryTest extends TestCase {
  public function testGetValueCallsGivenCallable() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $argumentsResolverMock = $this->createMock(ArgumentsResolverInterface::class);
    $argumentsResolverMock->method('resolve')->willReturn([]);
    $factory = new FactoryEntry(function() {
      return 'ok';
    });
    $this->assertSame('ok', $factory->getValue($containerMock, $argumentsResolverMock));
  }

  public function testGetValueCallsGivenCallableEverytime() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $argumentsResolverMock = $this->createMock(ArgumentsResolverInterface::class);
    $argumentsResolverMock->method('resolve')->willReturn([]);
    $callCount = 0;
    $factory = new FactoryEntry(function() use (&$callCount) {
      ++$callCount;
      return 'ok';
    });
    $factory->getValue($containerMock, $argumentsResolverMock);
    $factory->getValue($containerMock, $argumentsResolverMock);
    $this->assertSame(2, $callCount);
  }

  public function testArugmentsResolved() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $argumentsResolverMock = $this->createMock(ArgumentsResolverInterface::class);
    $argumentsResolverMock->method('resolve')->willReturn([1, 2]);
    $factory = new FactoryEntry(function($a, $b) {
      return [$a, $b];
    });
    $this->assertSame([1, 2], $factory->getValue($containerMock, $argumentsResolverMock));
  }
}
