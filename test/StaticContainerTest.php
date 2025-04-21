<?php

use PHPUnit\Framework\TestCase;
use Coroq\Container\StaticContainer;
use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\Exception\CircularDependencyException;
use Coroq\Container\Exception\NotFoundException;

/**
 * @covers Coroq\Container\StaticContainer
 */
class StaticContainerTest extends TestCase {
  private $mockResolver;

  protected function setUp(): void {
    $this->mockResolver = $this->createMock(ArgumentsResolverInterface::class);
  }

  public function testSetAndGetValue(): void {
    $container = new StaticContainer($this->mockResolver);
    $container->setValue('config', 'configuration value');

    $this->assertSame('configuration value', $container->get('config'));
  }

  public function testGetThrowsNotFoundException(): void {
    $container = new StaticContainer($this->mockResolver);

    $this->expectException(NotFoundException::class);
    $container->get('non_existent_entry');
  }

  public function testHasReturnsTrueForSetValue(): void {
    $container = new StaticContainer($this->mockResolver);
    $container->setValue('config', 'configuration value');

    $this->assertTrue($container->has('config'));
  }

  public function testHasReturnsFalseForNonExistentValue(): void {
    $container = new StaticContainer($this->mockResolver);

    $this->assertFalse($container->has('non_existent_entry'));
  }

  public function testSetAndGetFactoryEntry(): void {
    $container = new StaticContainer($this->mockResolver);
    $container->setFactory('service', function() {
      return new stdClass();
    });

    $result = $container->get('service');
    $this->assertInstanceOf(stdClass::class, $result);
  }

  public function testSetAndGetSingletonFactoryEntry(): void {
    $container = new StaticContainer($this->mockResolver);
    $container->setSingletonFactory('singleton_service', function() {
      return new stdClass();
    });

    $firstInstance = $container->get('singleton_service');
    $secondInstance = $container->get('singleton_service');
    $this->assertSame($firstInstance, $secondInstance);
  }

  public function testSetAndGetClassEntry(): void {
    $container = new StaticContainer($this->mockResolver);
    $container->setClass('stdClass', stdClass::class);

    $result = $container->get('stdClass');
    $this->assertInstanceOf(stdClass::class, $result);
  }

  public function testSetAndGetSingletonClassEntry(): void {
    $container = new StaticContainer($this->mockResolver);
    $container->setSingletonClass('singleton_class', stdClass::class);

    $firstInstance = $container->get('singleton_class');
    $secondInstance = $container->get('singleton_class');

    $this->assertSame($firstInstance, $secondInstance);
  }

public function testSetAndGetAliasEntry(): void {
    $container = new StaticContainer($this->mockResolver);
    $container->setValue('original_service', 'original value');
    $container->setAlias('alias_service', 'original_service');
    $result = $container->get('alias_service');
    $this->assertSame('original value', $result);
}

  public function testRecursionDetectionThrowsException(): void {
    $container = new StaticContainer($this->mockResolver);
    $container->setFactory('recursive_entry', function() use ($container) {
      $container->get('recursive_entry');
    });

    $this->expectException(CircularDependencyException::class);
    $container->get('recursive_entry');
  }
}
