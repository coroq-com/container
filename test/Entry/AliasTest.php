<?php
declare(strict_types=1);
use Coroq\Container\Entry\AliasEntry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Coroq\Container\Entry\AliasEntry
 */
class AliasTest extends TestCase {
  public function testGetValueRetrievesTargetValueFromContainer() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->method('get')
      ->with('target')
      ->willReturn('ok');
    $alias = new AliasEntry('target');
    $this->assertEquals('ok', $alias->getValue($containerMock));
  }
}
