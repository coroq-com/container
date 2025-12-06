<?php

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Coroq\Container\ArgumentsResolver\NameBasedArgumentsResolver;
use Coroq\Container\Exception\AutowiringException;
use Coroq\Container\Exception\NotFoundException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @covers Coroq\Container\ArgumentsResolver\NameBasedArgumentsResolver
 */
class NameBasedArgumentsResolverTest extends TestCase {
  private $mockContainer;
  private NameBasedArgumentsResolver $resolver;

  protected function setUp(): void {
    $this->mockContainer = $this->createMock(ContainerInterface::class);
    $this->resolver = new NameBasedArgumentsResolver();
    $this->resolver->setContainer($this->mockContainer);
  }

  public function testResolveArgumentsOfClassWithoutConstructor(): void {
    $arguments = $this->resolver->resolveConstructorArguments(NameBasedSampleService::class);

    $this->assertSame([], $arguments);
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

    $arguments = $this->resolver->resolveConstructorArguments(NameBasedSampleController::class);

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

    $arguments = $this->resolver->resolveCallableArguments(function ($service) {});

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

    $arguments = $this->resolver->resolveCallableArguments(function ($config, $logger) {});

    $this->assertCount(2, $arguments);
    $this->assertSame(['key' => 'value'], $arguments[0]);
    $this->assertInstanceOf(NameBasedSampleService::class, $arguments[1]);
  }

  public function testUsesDefaultValueIfNotInContainer(): void {
    $this->mockContainer
      ->method('has')
      ->with('service')
      ->willReturn(false);

    $arguments = $this->resolver->resolveCallableArguments(function ($service = 'default value') {});

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

    $this->resolver->resolveCallableArguments(function ($service) {});
  }

  public function testThrowsExceptionForVariadicParameter(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('Variadic parameter $handlers is not supported');

    $this->resolver->resolveCallableArguments(function (...$handlers) {});
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

    $this->resolver->resolveCallableArguments(function ($logger, ...$handlers) {});
  }

  public function testThrowsLogicExceptionIfContainerNotSet(): void {
    $resolver = new NameBasedArgumentsResolver();

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Container is not set');

    $resolver->resolveCallableArguments(function ($service) {});
  }
}

// Sample classes for testing
class NameBasedSampleService {
}

class NameBasedSampleController {
  public function __construct($service) {
  }
}
