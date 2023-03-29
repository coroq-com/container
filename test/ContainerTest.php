<?php
declare(strict_types=1);

use Coroq\Container\Container;
use Coroq\Container\Entry\EntryInterface;
use Coroq\Container\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase {
  public function testGetAndSet(): void {
    $container = new Container();
    $entryMock = $this->createMock(EntryInterface::class);
    $entryMock->method('getValue')->willReturn('Hello, world!');

    $container->set('mockEntry', $entryMock);

    $this->assertTrue($container->has('mockEntry'));
    $this->assertEquals('Hello, world!', $container->get('mockEntry'));
  }

  public function testGetValueFromEntry(): void {
    $container = new Container();
    $entryMock = $this->createMock(EntryInterface::class);
    $entryMock->expects($this->once())
      ->method('getValue')
      ->with($container)
      ->willReturn('Hello, world!');

    $container->set('mockEntry', $entryMock);

    $this->assertEquals('Hello, world!', $container->get('mockEntry'));
  }

  public function testNotFoundException(): void {
    $container = new Container();
    $this->assertFalse($container->has('nonexistent'));
    $this->expectException(NotFoundException::class);
    $container->get('nonexistent');
  }
}
