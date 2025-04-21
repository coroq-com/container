<?php

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Coroq\Container\CompositeContainer;
use Coroq\Container\Exception\NotFoundException;

/**
 * @covers Coroq\Container\CompositeContainer
 */
class CompositeContainerTest extends TestCase {
  public function testAddContainer(): void {
    $compositeContainer = new CompositeContainer();

    $mockContainer = $this->createMock(ContainerInterface::class);
    $compositeContainer->addContainer($mockContainer);

    $reflection = new ReflectionClass($compositeContainer);
    $containers = $reflection->getProperty('containers');
    $containers->setAccessible(true);
    $this->assertCount(1, $containers->getValue($compositeContainer));
  }

  public function testHasReturnsTrueIfEntryExistsInFirstContainer(): void {
    $compositeContainer = new CompositeContainer();

    $mockContainer1 = $this->createMock(ContainerInterface::class);
    $mockContainer1->method('has')->with('some_id')->willReturn(true);

    $mockContainer2 = $this->createMock(ContainerInterface::class);
    $mockContainer2->method('has')->with('some_id')->willReturn(false);

    $compositeContainer->addContainer($mockContainer1);
    $compositeContainer->addContainer($mockContainer2);

    $this->assertTrue($compositeContainer->has('some_id'));
  }

  public function testHasReturnsTrueIfEntryExistsInSecondContainer(): void {
    $compositeContainer = new CompositeContainer();

    $mockContainer1 = $this->createMock(ContainerInterface::class);
    $mockContainer1->method('has')->with('some_id')->willReturn(false);

    $mockContainer2 = $this->createMock(ContainerInterface::class);
    $mockContainer2->method('has')->with('some_id')->willReturn(true);

    $compositeContainer->addContainer($mockContainer1);
    $compositeContainer->addContainer($mockContainer2);

    $this->assertTrue($compositeContainer->has('some_id'));
  }

  public function testHasReturnsFalseIfEntryNotFoundInAnyContainer(): void {
    $compositeContainer = new CompositeContainer();

    $mockContainer1 = $this->createMock(ContainerInterface::class);
    $mockContainer1->method('has')->with('some_id')->willReturn(false);

    $mockContainer2 = $this->createMock(ContainerInterface::class);
    $mockContainer2->method('has')->with('some_id')->willReturn(false);

    $compositeContainer->addContainer($mockContainer1);
    $compositeContainer->addContainer($mockContainer2);

    $this->assertFalse($compositeContainer->has('some_id'));
  }

  public function testGetReturnsValueFromFirstContainer(): void {
    $compositeContainer = new CompositeContainer();

    $mockContainer1 = $this->createMock(ContainerInterface::class);
    $mockContainer1->method('has')->with('some_id')->willReturn(true);
    $mockContainer1->method('get')->with('some_id')->willReturn('value_from_first_container');

    $mockContainer2 = $this->createMock(ContainerInterface::class);

    $compositeContainer->addContainer($mockContainer1);
    $compositeContainer->addContainer($mockContainer2);

    $this->assertSame('value_from_first_container', $compositeContainer->get('some_id'));
  }

  public function testGetReturnsValueFromSecondContainer(): void {
    $compositeContainer = new CompositeContainer();

    $mockContainer1 = $this->createMock(ContainerInterface::class);
    $mockContainer1->method('has')->with('some_id')->willReturn(false);

    $mockContainer2 = $this->createMock(ContainerInterface::class);
    $mockContainer2->method('has')->with('some_id')->willReturn(true);
    $mockContainer2->method('get')->with('some_id')->willReturn('value_from_second_container');

    $compositeContainer->addContainer($mockContainer1);
    $compositeContainer->addContainer($mockContainer2);

    $this->assertSame('value_from_second_container', $compositeContainer->get('some_id'));
  }

  public function testGetThrowsNotFoundExceptionIfEntryNotFoundInAnyContainer(): void {
    $compositeContainer = new CompositeContainer();

    $mockContainer1 = $this->createMock(ContainerInterface::class);
    $mockContainer1->method('get')->with('some_id')->willThrowException(new NotFoundException());

    $mockContainer2 = $this->createMock(ContainerInterface::class);
    $mockContainer2->method('get')->with('some_id')->willThrowException(new NotFoundException());

    $compositeContainer->addContainer($mockContainer1);
    $compositeContainer->addContainer($mockContainer2);

    $this->expectException(NotFoundException::class);
    $compositeContainer->get('some_id');
  }
}
