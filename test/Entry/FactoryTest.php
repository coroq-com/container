<?php
declare(strict_types=1);
use Coroq\Container\Entry\Factory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FactoryTest extends TestCase {
  public function testGetValueCallsGivenCallable() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $factory = new Factory(function() {
      return 'ok';
    });
    $this->assertEquals('ok', $factory->getValue($containerMock));
  }

  public function testGetValueCallsGivenCallableEverytime() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $callCount = 0;
    $singleton = new Factory(function() use (&$callCount) {
      ++$callCount;
      return 'ok';
    });
    $singleton->getValue($containerMock);
    $singleton->getValue($containerMock);
    $this->assertEquals(2, $callCount);
  }
}
