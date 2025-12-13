<?php
declare(strict_types=1);

use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\StaticContainer\AliasEntry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Coroq\Container\StaticContainer\AliasEntry
 */
class AliasTest extends TestCase {
  public function testGetValueRetrievesTargetValueFromContainer() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->method('get')
      ->with('target')
      ->willReturn('ok');
    $argumentsResolverMock = $this->createMock(ArgumentsResolverInterface::class);
    $alias = new AliasEntry('target');
    $this->assertEquals('ok', $alias->getValue($containerMock, $argumentsResolverMock));
  }
}
