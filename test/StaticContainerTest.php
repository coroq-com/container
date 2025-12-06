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
  private StaticContainer $container;

  protected function setUp(): void {
    $this->mockResolver = $this->createMock(ArgumentsResolverInterface::class);
    $this->container = new StaticContainer();
    $this->container->setArgumentsResolver($this->mockResolver);
  }

  public function testSetAndGetValue(): void {
    $this->container->setValue('config', 'configuration value');

    $this->assertSame('configuration value', $this->container->get('config'));
  }

  public function testGetThrowsNotFoundException(): void {
    $this->expectException(NotFoundException::class);
    $this->container->get('non_existent_entry');
  }

  public function testHasReturnsTrueForSetValue(): void {
    $this->container->setValue('config', 'configuration value');

    $this->assertTrue($this->container->has('config'));
  }

  public function testHasReturnsFalseForNonExistentValue(): void {
    $this->assertFalse($this->container->has('non_existent_entry'));
  }

  public function testSetAndGetFactoryEntry(): void {
    $this->container->setFactory('service', function() {
      return new stdClass();
    });

    $result = $this->container->get('service');
    $this->assertInstanceOf(stdClass::class, $result);
  }

  public function testSetAndGetSingletonFactoryEntry(): void {
    $this->container->setSingletonFactory('singleton_service', function() {
      return new stdClass();
    });

    $firstInstance = $this->container->get('singleton_service');
    $secondInstance = $this->container->get('singleton_service');
    $this->assertSame($firstInstance, $secondInstance);
  }

  public function testSetAndGetClassEntry(): void {
    $this->container->setClass('stdClass', stdClass::class);

    $result = $this->container->get('stdClass');
    $this->assertInstanceOf(stdClass::class, $result);
  }

  public function testSetAndGetSingletonClassEntry(): void {
    $this->container->setSingletonClass('singleton_class', stdClass::class);

    $firstInstance = $this->container->get('singleton_class');
    $secondInstance = $this->container->get('singleton_class');

    $this->assertSame($firstInstance, $secondInstance);
  }

  public function testSetAndGetAliasEntry(): void {
    $this->container->setValue('original_service', 'original value');
    $this->container->setAlias('alias_service', 'original_service');
    $result = $this->container->get('alias_service');
    $this->assertSame('original value', $result);
  }

  public function testRecursionDetectionThrowsException(): void {
    $container = $this->container;
    $this->container->setFactory('recursive_entry', function() use ($container) {
      $container->get('recursive_entry');
    });

    $this->expectException(CircularDependencyException::class);
    $this->container->get('recursive_entry');
  }

  public function testThrowsLogicExceptionIfArgumentsResolverNotSet(): void {
    $container = new StaticContainer();

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('ArgumentsResolver is not set');

    $container->setFactory('service', function() { return new stdClass(); });
  }
}
