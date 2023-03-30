<?php
declare(strict_types=1);
use Coroq\Container\Entry\Value;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ValueTest extends TestCase {
  public function testGetValueReturnsGivenValue() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $value = new Value('ok');
    $this->assertEquals('ok', $value->getValue($containerMock));
  }
}
