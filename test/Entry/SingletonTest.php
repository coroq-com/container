<?php
declare(strict_types=1);

use Coroq\Container\Entry\EntryInterface;
use Coroq\Container\Entry\Singleton;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class SingletonTest extends TestCase {
  public function testGetValueCallsGivenCallable() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $entryMock = $this->createMock(EntryInterface::class);
    $entryMock
      ->method('getValue')
      ->willReturn('ok');
    $singletonEntry = new Singleton($entryMock);
    $this->assertEquals('ok', $singletonEntry->getValue($containerMock));
  }

  public function testGetValueCallsGivenCallableOnce() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $entryMock = $this->createMock(EntryInterface::class);
    $entryMock
      ->expects($this->once())
      ->method('getValue')
      ->willReturn(1, 2);
    $singletonEntry = new Singleton($entryMock);
    $this->assertEquals(1, $singletonEntry->getValue($containerMock));
    $this->assertEquals(1, $singletonEntry->getValue($containerMock));
  }
}
