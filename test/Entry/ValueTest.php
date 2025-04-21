<?php
declare(strict_types=1);
use Coroq\Container\Entry\ValueEntry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Coroq\Container\Entry\ValueEntry
 */
class ValueTest extends TestCase {
  public function testGetValueReturnsGivenValue() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $value = new ValueEntry('ok');
    $this->assertEquals('ok', $value->getValue($containerMock));
  }
}
