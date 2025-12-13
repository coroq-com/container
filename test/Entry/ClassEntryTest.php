<?php
declare(strict_types=1);

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\StaticContainer\ClassEntry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Coroq\Container\StaticContainer\ClassEntry
 */
class ClassEntryTest extends TestCase {
  public function testInstantiation() {
    $argumentsResolver = $this->createMock(ArgumentsResolverInterface::class);
    $argumentsResolver
      ->method('resolve')
      ->willReturn([new SampleValueClass()]);
    $container = $this->createMock(ContainerInterface::class);
    $entry = new ClassEntry(SampleClassWithConstructor::class);
    $value = $entry->getValue($container, $argumentsResolver);
    $this->assertInstanceOf(SampleClassWithConstructor::class, $value);
    $this->assertInstanceOf(SampleValueClass::class, $value->testValue);
  }

  public function testInstantiationWithoutConstructor() {
    $argumentsResolver = $this->createMock(ArgumentsResolverInterface::class);
    $argumentsResolver
      ->expects($this->never())
      ->method('resolve');
    $container = $this->createMock(ContainerInterface::class);
    $entry = new ClassEntry(SampleValueClass::class);
    $value = $entry->getValue($container, $argumentsResolver);
    $this->assertInstanceOf(SampleValueClass::class, $value);
  }
}

class SampleValueClass {
}

class SampleClassWithConstructor {
  public $testValue;
  public function __construct(SampleValueClass $testValue) {
    $this->testValue = $testValue;
  }
}
