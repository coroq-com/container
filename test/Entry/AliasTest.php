<?php
declare(strict_types=1);
use Coroq\Container\Entry\Alias;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class AliasTest extends TestCase {
  public function testGetValueRetrievesTargetValueFromContainer() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->method('get')
      ->with('target')
      ->willReturn('ok');
    $alias = new Alias('target');
    $this->assertEquals('ok', $alias->getValue($containerMock));
  }
}
