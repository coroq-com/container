<?php
declare(strict_types=1);

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\StaticContainer\ValueEntry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Coroq\Container\StaticContainer\ValueEntry
 */
class ValueTest extends TestCase {
  public function testGetValueReturnsGivenValue() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $argumentsResolverMock = $this->createMock(ArgumentsResolverInterface::class);
    $value = new ValueEntry('ok');
    $this->assertEquals('ok', $value->getValue($containerMock, $argumentsResolverMock));
  }
}
