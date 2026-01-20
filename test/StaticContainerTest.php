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
    $container->setFactory('service', function() { return new stdClass(); });

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('ArgumentsResolver is not set');

    // Exception is thrown when getting, not when setting
    $container->get('service');
  }

  public function testSetAndGetRawValue(): void {
    $this->container->set('raw', 'raw value');

    $this->assertTrue($this->container->has('raw'));
    $this->assertSame('raw value', $this->container->get('raw'));
  }

  public function testSetAndGetRawObject(): void {
    $obj = new stdClass();
    $obj->name = 'test';
    $this->container->set('obj', $obj);

    $this->assertTrue($this->container->has('obj'));
    $this->assertSame($obj, $this->container->get('obj'));
  }

  public function testSetClassWithNonExistentClassThrowsInstantiationException(): void {
    $this->container->setClass('service', 'NonExistentClass');

    $this->assertTrue($this->container->has('service'));

    $this->expectException(\Coroq\Container\Exception\InstantiationException::class);
    $this->expectExceptionMessage("Cannot instantiate: class 'NonExistentClass' does not exist");
    $this->container->get('service');
  }

  public function testSetClassWithInterfaceThrowsInstantiationException(): void {
    $this->container->setClass('service', TestInterface::class);

    $this->assertTrue($this->container->has('service'));

    $this->expectException(\Coroq\Container\Exception\InstantiationException::class);
    $this->expectExceptionMessage("is not instantiable");
    $this->container->get('service');
  }

  public function testSetClassWithAbstractClassThrowsInstantiationException(): void {
    $this->container->setClass('service', AbstractTestClass::class);

    $this->assertTrue($this->container->has('service'));

    $this->expectException(\Coroq\Container\Exception\InstantiationException::class);
    $this->expectExceptionMessage("is not instantiable");
    $this->container->get('service');
  }
}

interface TestInterface {
}

abstract class AbstractTestClass {
}
