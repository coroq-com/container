<?php
declare(strict_types=1);

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\StaticContainer\EntryInterface;
use Coroq\Container\StaticContainer\SingletonEntry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Coroq\Container\StaticContainer\SingletonEntry
 */
class SingletonTest extends TestCase {
  public function testGetValueCallsGivenCallable() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $argumentsResolverMock = $this->createMock(ArgumentsResolverInterface::class);
    $entryMock = $this->createMock(EntryInterface::class);
    $entryMock
      ->method('getValue')
      ->willReturn('ok');
    $singletonEntry = new SingletonEntry($entryMock);
    $this->assertEquals('ok', $singletonEntry->getValue($containerMock, $argumentsResolverMock));
  }

  public function testGetValueCallsGivenCallableOnce() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $argumentsResolverMock = $this->createMock(ArgumentsResolverInterface::class);
    $entryMock = $this->createMock(EntryInterface::class);
    $entryMock
      ->expects($this->once())
      ->method('getValue')
      ->willReturn(1, 2);
    $singletonEntry = new SingletonEntry($entryMock);
    $this->assertEquals(1, $singletonEntry->getValue($containerMock, $argumentsResolverMock));
    $this->assertEquals(1, $singletonEntry->getValue($containerMock, $argumentsResolverMock));
  }
}
