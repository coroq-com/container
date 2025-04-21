<?php
declare(strict_types=1);

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\Entry\ClassEntry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Coroq\Container\Entry\ClassEntry
 */
class ClassEntryTest extends TestCase {
  public function testInstantiation() {
    $argumentsResolver = $this->createMock(ArgumentsResolverInterface::class);
    $argumentsResolver
      ->method('resolveConstructorArguments')
      ->willReturn([new SampleValueClass()]);
    $container = $this->createMock(ContainerInterface::class);
    $entry = new ClassEntry($argumentsResolver, SampleClassWithConstructor::class);
    $value = $entry->getValue($container);
    $this->assertInstanceOf(SampleClassWithConstructor::class, $value);
    $this->assertInstanceOf(SampleValueClass::class, $value->testValue);
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
