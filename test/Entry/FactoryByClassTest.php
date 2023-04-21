<?php
declare(strict_types=1);
use Coroq\Container\Entry\FactoryByClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FactoryByClassTest extends TestCase {
  public function testInstantiationWithoutConstructor() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $entry = new FactoryByClass(FactoryByClassSampleWithoutConstructor::class);
    $sample = $entry->getValue($containerMock);
    $this->assertInstanceOf(FactoryByClassSampleWithoutConstructor::class, $sample);
  }

  public function testInstantiationWithConstructor() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->method('get')
      ->with('testValue')
      ->willReturn('ok');
    $entry = new FactoryByClass(FactoryByClassSampleWithConstructor::class);
    $sample = $entry->getValue($containerMock);
    $this->assertInstanceOf(FactoryByClassSampleWithConstructor::class, $sample);
    $this->assertEquals('ok', $sample->getTestValue());
  }

  public function testInstantiationOccursEverytime() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->method('get')
      ->with('testValue')
      ->willReturn(1, 2);
    $entry = new FactoryByClass(FactoryByClassSampleWithConstructor::class);
    $this->assertEquals(1, $entry->getValue($containerMock)->getTestValue());
    $this->assertEquals(2, $entry->getValue($containerMock)->getTestValue());
  }
}

class FactoryByClassSampleWithoutConstructor {
}

class FactoryByClassSampleWithConstructor {
  private $testValue;
  public function __construct($testValue) {
    $this->testValue = $testValue;
  }
  public function getTestValue() {
    return $this->testValue;
  }
}
