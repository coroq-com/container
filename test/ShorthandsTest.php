<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function Coroq\Container\alias;
use function Coroq\Container\factory;
use function Coroq\Container\singleton;
use function Coroq\Container\spread;
use function Coroq\Container\value;

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
    $this->assertInstanceOf(\Coroq\Container\Entry\Factory::class, $factory);
    $this->assertEquals('ok', $factory->getValue($containerMock));
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
    $this->assertInstanceOf(\Coroq\Container\Entry\Singleton::class, $singleton);
    $this->assertEquals('ok', $singleton->getValue($containerMock));
  }

  public function testValue() {
    $value = value('ok');
    $this->assertInstanceOf(\Coroq\Container\Entry\Value::class, $value);
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
