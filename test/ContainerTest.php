<?php
declare(strict_types=1);
use Coroq\Container\Container;
use Coroq\Container\Entry\EntryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase {
  public function testSetAndGetWithEntryInterface() {
    $container = new Container();
    $entryMock = $this->createMock(EntryInterface::class);
    $entryMock->expects($this->once())
      ->method('getValue')
      ->with($container)
      ->willReturn('entry_value');
    $container->set('entry_key', $entryMock);
    $this->assertEquals('entry_value', $container->get('entry_key'));
  }

  public function testSetAndGetWithString() {
    $container = new Container();
    $container->set('entry_key', 'value');
    $this->assertEquals('value', $container->get('entry_key'));
  }

  public function testHasEntry() {
    $container = new Container();
    $entryMock = $this->createMock(EntryInterface::class);
    $container->set('entry_key', $entryMock);
    $this->assertTrue($container->has('entry_key'));
    $this->assertFalse($container->has('non_existent_key'));
  }

  public function testGetThrowsNotFoundException() {
    $container = new Container();
    $this->expectException(NotFoundExceptionInterface::class);
    $container->get('non_existent_key');
  }
}
