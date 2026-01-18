<?php

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Coroq\Container\ArgumentsResolver\TypeBasedArgumentsResolver;
use Coroq\Container\Exception\AutowiringException;
use Coroq\Container\Exception\NotFoundException;
use Psr\Container\NotFoundExceptionInterface;
use Coroq\CallableReflector\CallableReflector;

/**
 * @covers Coroq\Container\ArgumentsResolver\TypeBasedArgumentsResolver
 */
class TypeBasedArgumentsResolverTest extends TestCase {
  private $mockContainer;
  private TypeBasedArgumentsResolver $resolver;

  protected function setUp(): void {
    $this->mockContainer = $this->createMock(ContainerInterface::class);
    $this->resolver = new TypeBasedArgumentsResolver();
  }

  public function testResolveArgumentsOfClassWithoutConstructor(): void {
    $class = new ReflectionClass(SampleService::class);
    $constructor = $class->getConstructor();

    // No constructor, so we don't call resolve
    $this->assertNull($constructor);
  }

  public function testResolveArgumentsOfConstructor(): void {
    $this->mockContainer
      ->expects($this->once())
      ->method('get')
      ->with(SampleService::class)
      ->willReturn(new SampleService());

    $class = new ReflectionClass(SampleController::class);
    $constructor = $class->getConstructor();
    $arguments = $this->resolver->resolve($constructor, $this->mockContainer);

    $this->assertCount(1, $arguments);
    $this->assertInstanceOf(SampleService::class, $arguments[0]);
  }

  public function testResolveArgumentsOfCallable(): void {
    $this->mockContainer
      ->expects($this->once())
      ->method('get')
      ->with(SampleService::class)
      ->willReturn(new SampleService());

    $callable = function (SampleService $service) {};
    $reflection = CallableReflector::createFromCallable($callable);
    $arguments = $this->resolver->resolve($reflection, $this->mockContainer);

    $this->assertCount(1, $arguments);
    $this->assertInstanceOf(SampleService::class, $arguments[0]);
  }

  public function testThrowsExceptionForMissingTypeDeclaration(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('lacks a type declaration');

    $callable = function ($service) {};
    $reflection = CallableReflector::createFromCallable($callable);
    $this->resolver->resolve($reflection, $this->mockContainer);
  }

  public function testThrowsExceptionForBuiltInType(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('built-in type');

    $reflection = new \ReflectionMethod(SampleController::class, 'staticMethod');
    $this->resolver->resolve($reflection, $this->mockContainer);
  }

  /**
   * @requires PHP >= 8.0
   */
  public function testThrowsExceptionForComplexType(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('complex type declaration');

    eval('$closure = function(int|stdClass $arg) {};');
    $reflection = CallableReflector::createFromCallable($closure);
    $this->resolver->resolve($reflection, $this->mockContainer);
  }

  public function testExceptionForMethod(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('$number in SampleController::staticMethod is of a built-in type');

    $reflection = new \ReflectionMethod(SampleController::class, 'staticMethod');
    $this->resolver->resolve($reflection, $this->mockContainer);
  }

  public function testThrowsNotFoundExceptionIfTheTypeNotFoundInContainer(): void {
    $this->mockContainer
      ->expects($this->once())
      ->method('get')
      ->with(SampleService::class)
      ->willThrowException(new NotFoundException());

    $this->expectException(NotFoundExceptionInterface::class);

    $class = new ReflectionClass(SampleController::class);
    $constructor = $class->getConstructor();
    $this->resolver->resolve($constructor, $this->mockContainer);
  }

  public function testUsesDefaultValueIfAvailable(): void {
    $callable = function ($service = 'default value') {};
    $reflection = CallableReflector::createFromCallable($callable);
    $arguments = $this->resolver->resolve($reflection, $this->mockContainer);

    $this->assertCount(1, $arguments);
    $this->assertSame('default value', $arguments[0]);
  }

  public function testThrowsExceptionForVariadicParameter(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('Variadic parameter $handlers is not supported');

    $callable = function (SampleService ...$handlers) {};
    $reflection = CallableReflector::createFromCallable($callable);
    $this->resolver->resolve($reflection, $this->mockContainer);
  }

  public function testThrowsExceptionForVariadicParameterAfterRegularParameters(): void {
    $this->mockContainer
      ->method('get')
      ->with(SampleService::class)
      ->willReturn(new SampleService());

    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('Variadic parameter $handlers is not supported');

    $callable = function (SampleService $service, SampleService ...$handlers) {};
    $reflection = CallableReflector::createFromCallable($callable);
    $this->resolver->resolve($reflection, $this->mockContainer);
  }
}

// Sample classes for testing
class SampleService {
}

class SampleController {
  public function __construct(SampleService $service) {
  }

  public static function staticMethod(int $number) {
  }
}

function sampleFunction() {

}
