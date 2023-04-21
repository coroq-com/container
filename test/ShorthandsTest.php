<?php
declare(strict_types=1);

use Coroq\Container\Entry\Factory;
use Coroq\Container\Entry\FactoryByClass;
use Coroq\Container\Entry\Singleton;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function Coroq\Container\alias;
use function Coroq\Container\factory;
use function Coroq\Container\factoryByClass;
use function Coroq\Container\singleton;
use function Coroq\Container\singletonByClass;
use function Coroq\Container\spread;

class ShorthandsTest extends TestCase {
  public function testFactory() {
    $factory = factory(function($ok) {
      return $ok;
    });
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->method('get')
      ->with('ok')
      ->willReturn('ok');
    $this->assertInstanceOf(Factory::class, $factory);
    $this->assertEquals('ok', $factory->getValue($containerMock));
  }

  public function testFactoryByClass() {
    $entry = factoryByClass(FactoryByClassShorthandSample::class);
    $this->assertInstanceOf(FactoryByClass::class, $entry);
  }

  public function testSingleton() {
    $singleton = singleton(function($ok) {
      return $ok;
    });
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->method('get')
      ->with('ok')
      ->willReturn('ok');
    $this->assertInstanceOf(Singleton::class, $singleton);
    $this->assertEquals('ok', $singleton->getValue($containerMock));
  }

  public function testSingletonByClass() {
    $singleton = singletonByClass(FactoryByClassShorthandSample::class);
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->method('get')
      ->with('testValue')
      ->willReturn('ok');
    $this->assertInstanceOf(Singleton::class, $singleton);
    $this->assertEquals('ok', $singleton->getValue($containerMock)->getTestValue());
  }

  public function testAlias() {
    $alias = alias('targetId');
    $this->assertInstanceOf(\Coroq\Container\Entry\Alias::class, $alias);
  }

  public function testSpread() {
    $spread = spread(function() {
      return 'ok';
    });
    $containerMock = $this->createMock(ContainerInterface::class);
    $this->assertInstanceOf(\Coroq\Container\SpreadArguments::class, $spread);
    $this->assertEquals('ok', $spread($containerMock));
  }
}

class FactoryByClassShorthandSample {
  private $testValue;
  public function __construct($testValue) {
    $this->testValue = $testValue;
  }
  public function getTestValue() {
    return $this->testValue;
  }
}
