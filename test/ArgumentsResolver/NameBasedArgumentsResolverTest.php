<?php

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Coroq\Container\ArgumentsResolver\NameBasedArgumentsResolver;
use Coroq\Container\Exception\AutowiringException;
use Coroq\Container\Exception\NotFoundException;
use Psr\Container\NotFoundExceptionInterface;
use Coroq\CallableReflector\CallableReflector;

/**
 * @covers Coroq\Container\ArgumentsResolver\NameBasedArgumentsResolver
 */
class NameBasedArgumentsResolverTest extends TestCase {
  private $mockContainer;
  private NameBasedArgumentsResolver $resolver;

  protected function setUp(): void {
    $this->mockContainer = $this->createMock(ContainerInterface::class);
    $this->resolver = new NameBasedArgumentsResolver();
  }

  public function testResolveArgumentsOfClassWithoutConstructor(): void {
    $class = new ReflectionClass(NameBasedSampleService::class);
    $constructor = $class->getConstructor();

    // No constructor, so we don't call resolve
    $this->assertNull($constructor);
  }

  public function testResolveArgumentsOfConstructorByParameterName(): void {
    $this->mockContainer
      ->method('has')
      ->with('service')
      ->willReturn(true);

    $this->mockContainer
      ->expects($this->once())
      ->method('get')
      ->with('service')
      ->willReturn(new NameBasedSampleService());

    $class = new ReflectionClass(NameBasedSampleController::class);
    $constructor = $class->getConstructor();
    $arguments = $this->resolver->resolve($constructor, $this->mockContainer);

    $this->assertCount(1, $arguments);
    $this->assertInstanceOf(NameBasedSampleService::class, $arguments[0]);
  }

  public function testResolveArgumentsOfCallableByParameterName(): void {
    $this->mockContainer
      ->method('has')
      ->with('service')
      ->willReturn(true);

    $this->mockContainer
      ->expects($this->once())
      ->method('get')
      ->with('service')
      ->willReturn(new NameBasedSampleService());

    $callable = function ($service) {};
    $reflection = CallableReflector::createFromCallable($callable);
    $arguments = $this->resolver->resolve($reflection, $this->mockContainer);

    $this->assertCount(1, $arguments);
    $this->assertInstanceOf(NameBasedSampleService::class, $arguments[0]);
  }

  public function testResolveMultipleArgumentsByName(): void {
    $this->mockContainer
      ->method('has')
      ->willReturnMap([
        ['config', true],
        ['logger', true],
      ]);

    $this->mockContainer
      ->method('get')
      ->willReturnMap([
        ['config', ['key' => 'value']],
        ['logger', new NameBasedSampleService()],
      ]);

    $callable = function ($config, $logger) {};
    $reflection = CallableReflector::createFromCallable($callable);
    $arguments = $this->resolver->resolve($reflection, $this->mockContainer);

    $this->assertCount(2, $arguments);
    $this->assertSame(['key' => 'value'], $arguments[0]);
    $this->assertInstanceOf(NameBasedSampleService::class, $arguments[1]);
  }

  public function testUsesDefaultValueIfNotInContainer(): void {
    $this->mockContainer
      ->method('has')
      ->with('service')
      ->willReturn(false);

    $callable = function ($service = 'default value') {};
    $reflection = CallableReflector::createFromCallable($callable);
    $arguments = $this->resolver->resolve($reflection, $this->mockContainer);

    $this->assertCount(1, $arguments);
    $this->assertSame('default value', $arguments[0]);
  }

  public function testThrowsNotFoundExceptionIfNotInContainerAndNoDefault(): void {
    $this->mockContainer
      ->method('has')
      ->with('service')
      ->willReturn(false);

    $this->mockContainer
      ->method('get')
      ->with('service')
      ->willThrowException(new NotFoundException());

    $this->expectException(NotFoundExceptionInterface::class);

    $callable = function ($service) {};
    $reflection = CallableReflector::createFromCallable($callable);
    $this->resolver->resolve($reflection, $this->mockContainer);
  }

  public function testThrowsExceptionForVariadicParameter(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('Variadic parameter $handlers is not supported');

    $callable = function (...$handlers) {};
    $reflection = CallableReflector::createFromCallable($callable);
    $this->resolver->resolve($reflection, $this->mockContainer);
  }

  public function testThrowsExceptionForVariadicParameterAfterRegularParameters(): void {
    $this->mockContainer
      ->method('has')
      ->with('logger')
      ->willReturn(true);

    $this->mockContainer
      ->method('get')
      ->with('logger')
      ->willReturn(new NameBasedSampleService());

    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('Variadic parameter $handlers is not supported');

    $callable = function ($logger, ...$handlers) {};
    $reflection = CallableReflector::createFromCallable($callable);
    $this->resolver->resolve($reflection, $this->mockContainer);
  }
}

// Sample classes for testing
class NameBasedSampleService {
}

class NameBasedSampleController {
  public function __construct($service) {
  }
}
