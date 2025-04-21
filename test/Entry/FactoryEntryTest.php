<?php
declare(strict_types=1);

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\Entry\FactoryEntry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Coroq\Container\Entry\FactoryEntry
 */
class FactoryEntryTest extends TestCase {
  public function testGetValueCallsGivenCallable() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $argumentsResolverMock = $this->createMock(ArgumentsResolverInterface::class);
    $factory = new FactoryEntry($argumentsResolverMock, function() {
      return 'ok';
    });
    $this->assertSame('ok', $factory->getValue($containerMock));
  }

  public function testGetValueCallsGivenCallableEverytime() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $argumentsResolverMock = $this->createMock(ArgumentsResolverInterface::class);
    $callCount = 0;
    $singleton = new FactoryEntry($argumentsResolverMock, function() use (&$callCount) {
      ++$callCount;
      return 'ok';
    });
    $singleton->getValue($containerMock);
    $singleton->getValue($containerMock);
    $this->assertSame(2, $callCount);
  }

  public function testArugmentsResolved() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $argumentsResolverMock = $this->createMock(ArgumentsResolverInterface::class);
    $argumentsResolverMock->method('resolveCallableArguments')->willReturn([1, 2]);
    $factory = new FactoryEntry($argumentsResolverMock, function($a, $b) {
      return [$a, $b];
    });
    $this->assertSame([1, 2], $factory->getValue($containerMock));
  }
}
