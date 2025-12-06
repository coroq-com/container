<?php
use PHPUnit\Framework\TestCase;
use Coroq\Container\DynamicContainer;
use Coroq\Container\Exception\NotFoundException;
use Coroq\Container\ArgumentsResolver\ArgumentsResolverInterface;
use Coroq\Container\Exception\CircularDependencyException;
use Coroq\Test2\SampleClass2;
use Coroq\Test\RecursiveClass;
use Coroq\Test\SampleClass;
use Coroq\Test\SampleClassWithConstructor;

require_once __DIR__ . '/SampleClass.php';

/**
 * @covers Coroq\Container\DynamicContainer
 */
class DynamicContainerTest extends TestCase {
  public function testAddNamespaceWithTrailingBackslash(): void {
    $container = new DynamicContainer();
    $container->addNamespace('Coroq\\Test\\');
    $reflection = new ReflectionClass($container);
    $namespaces = $reflection->getProperty('namespaces');
    $namespaces->setAccessible(true);
    $this->assertContains('Coroq\\Test\\', $namespaces->getValue($container));
  }

  public function testAddNamespaceWithoutTrailingBackslash(): void {
    $container = new DynamicContainer();
    $container->addNamespace('Coroq\\Test');
    $reflection = new ReflectionClass($container);
    $namespaces = $reflection->getProperty('namespaces');
    $namespaces->setAccessible(true);
    $this->assertContains('Coroq\\Test\\', $namespaces->getValue($container));
  }

  public function testHasReturnsFalseIfClassNotFound(): void {
    $container = new DynamicContainer();
    $this->assertFalse($container->has('NonExistentClass'));
  }

  public function testHasReturnsTrueIfClassExistsInNamespace(): void {
    $container = new DynamicContainer();
    $container->addNamespace('Coroq\\Test');
    $this->assertTrue($container->has(SampleClass::class));
  }

  public function testHasReturnsTrueIfClassExistsInSecondNamespace(): void {
    $container = new DynamicContainer();
    $container->addNamespace('Coroq\\Test');
    $container->addNamespace('Coroq\\Test2');
    $this->assertTrue($container->has(SampleClass2::class));
  }

  public function testGetThrowsNotFoundExceptionIfNotInNamespace(): void {
    $container = new DynamicContainer();
    $this->expectException(NotFoundException::class);
    $container->get('NonExistentClass');
  }

  public function testGetInstantiatesClassIfInNamespace(): void {
    $argumentsResolver = $this->createMock(ArgumentsResolverInterface::class);
    $argumentsResolver->method('resolveConstructorArguments')->willReturn([1, 2]);
    $container = new DynamicContainer();
    $container->setArgumentsResolver($argumentsResolver);
    $container->addNamespace('Coroq\\Test');
    $result = $container->get(SampleClassWithConstructor::class);
    $this->assertInstanceOf(SampleClassWithConstructor::class, $result);
    $this->assertSame([1, 2], [$result->a, $result->b]);
  }

  public function testGetInstantiatesClassIfInSecondNamespace(): void {
    $argumentsResolver = $this->createMock(ArgumentsResolverInterface::class);
    $argumentsResolver->method('resolveConstructorArguments')->willReturn([]);
    $container = new DynamicContainer();
    $container->setArgumentsResolver($argumentsResolver);
    $container->addNamespace('Coroq\\Test');
    $container->addNamespace('Coroq\\Test2');
    $result = $container->get(SampleClass2::class);
    $this->assertInstanceOf(SampleClass2::class, $result);
  }

  public function testGetReturnsSameInstanceForSingleton(): void {
    $mockResolver = $this->createMock(ArgumentsResolverInterface::class);
    $mockResolver->method('resolveConstructorArguments')->willReturn([]);

    $container = new DynamicContainer();
    $container->setArgumentsResolver($mockResolver);
    $container->addNamespace('Coroq\\Test\\');

    $firstInstance = $container->get(SampleClass::class);
    $secondInstance = $container->get(SampleClass::class);

    $this->assertSame($firstInstance, $secondInstance);
  }

  public function testRecursionDetectionThrowsException(): void {
    $argumentsResolver = $this->createMock(ArgumentsResolverInterface::class);

    $container = new DynamicContainer();
    $container->setArgumentsResolver($argumentsResolver);

    $argumentsResolver->method('resolveConstructorArguments')->willReturnCallback(function () use ($container) {
      $container->get(RecursiveClass::class);
    });

    $container->addNamespace('Coroq\\Test');

    $this->expectException(CircularDependencyException::class);

    $container->get(RecursiveClass::class);
  }

  public function testThrowsLogicExceptionIfArgumentsResolverNotSet(): void {
    $container = new DynamicContainer();
    $container->addNamespace('Coroq\\Test');

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('ArgumentsResolver is not set');

    $container->get(SampleClass::class);
  }
}
