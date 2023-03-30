<?php
declare(strict_types=1);
use Coroq\Container\Entry\Singleton;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class SingletonTest extends TestCase {
  public function testGetValueCallsGivenCallable() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $factory = new Singleton(function() {
      return 'ok';
    });
    $this->assertEquals('ok', $factory->getValue($containerMock));
  }

  public function testGetValueCallsGivenCallableOnce() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $callCount = 0;
    $singleton = new Singleton(function() use (&$callCount) {
      ++$callCount;
      return 'ok';
    });
    $singleton->getValue($containerMock);
    $singleton->getValue($containerMock);
    $this->assertEquals(1, $callCount);
  }
}
