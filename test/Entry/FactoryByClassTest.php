<?php
declare(strict_types=1);
use Coroq\Container\Entry\FactoryByClass;
use Coroq\Container\Exception\NotFoundException;
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

  public function testDefaultArgumentIsUsedIfContainerDoesNotHaveIt() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->method('get')
      ->will($this->throwException(new NotFoundException));
    $entry = new FactoryByClass(FactoryByClassSampleWithConstructorWithDefaultIntegerArgument::class);
    $this->assertEquals(1, $entry->getValue($containerMock)->getTestValue1());
    $this->assertEquals(2, $entry->getValue($containerMock)->getTestValue2());
  }

  /**
   * @requires PHP 8.1
   */
  public function testDefaultObjectArgument() {
    require_once __DIR__ . '/FactoryByClassTestSampleForPhp81.php';
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->method('get')
      ->will($this->throwException(new NotFoundException));
    $entry = new FactoryByClass(FactoryByClassSampleWithConstructorWithDefaultObjectArgument::class);
    $this->assertInstanceOf(FactoryByClassSampleArgument::class, $entry->getValue($containerMock)->getObject());
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

class FactoryByClassSampleWithConstructorWithDefaultIntegerArgument {
  private $testValue1;
  private $testValue2;
  public function __construct($testValue1 = 1, $testValue2 = 2) {
    $this->testValue1 = $testValue1;
    $this->testValue2 = $testValue2;
  }
  public function getTestValue1() {
    return $this->testValue1;
  }
  public function getTestValue2() {
    return $this->testValue2;
  }
}
