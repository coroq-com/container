<?php

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Coroq\Container\ArgumentsResolver\TypeBasedArgumentsResolver;
use Coroq\Container\Exception\AutowiringException;
use Coroq\Container\Exception\NotFoundException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @covers Coroq\Container\ArgumentsResolver\TypeBasedArgumentsResolver
 */
class TypeBasedArgumentsResolverTest extends TestCase {
  private $mockContainer;

  protected function setUp(): void {
    $this->mockContainer = $this->createMock(ContainerInterface::class);
  }

  public function testResolveArgumentsOfClassWithoutConstructor(): void {
    $resolver = new TypeBasedArgumentsResolver($this->mockContainer);
    $arguments = $resolver->resolveConstructorArguments(SampleService::class);

    $this->assertSame([], $arguments);
  }

  public function testResolveArgumentsOfConstructor(): void {
    $this->mockContainer
      ->expects($this->once())
      ->method('get')
      ->with(SampleService::class)
      ->willReturn(new SampleService());

    $resolver = new TypeBasedArgumentsResolver($this->mockContainer);

    $arguments = $resolver->resolveConstructorArguments(SampleController::class);

    $this->assertCount(1, $arguments);
    $this->assertInstanceOf(SampleService::class, $arguments[0]);
  }

  public function testResolveArgumentsOfCallable(): void {
    $this->mockContainer
      ->expects($this->once())
      ->method('get')
      ->with(SampleService::class)
      ->willReturn(new SampleService());

    $resolver = new TypeBasedArgumentsResolver($this->mockContainer);
    $arguments = $resolver->resolveCallableArguments(function (SampleService $service) {});

    $this->assertCount(1, $arguments);
    $this->assertInstanceOf(SampleService::class, $arguments[0]);
  }

  public function testThrowsExceptionForMissingTypeDeclaration(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('lacks a type declaration');

    $resolver = new TypeBasedArgumentsResolver($this->mockContainer);
    $resolver->resolveCallableArguments(function ($service) {});
  }

  public function testThrowsExceptionForBuiltInType(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('built-in type');
    $resolver = new TypeBasedArgumentsResolver($this->mockContainer);
    $resolver->resolveCallableArguments([SampleController::class, 'staticMethod']);
  }

  /**
   * @requires PHP >= 8.0
   */
  public function testThrowsExceptionForComplexType(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('complex type declaration');
    $resolver = new TypeBasedArgumentsResolver($this->mockContainer);
    eval('$closure = function(int|stdClass $arg) {};');
    $resolver->resolveCallableArguments($closure);
  }

  public function testExceptionForMethod(): void {
    $this->expectException(AutowiringException::class);
    $this->expectExceptionMessage('$number in SampleController::staticMethod is of a built-in type');
    $resolver = new TypeBasedArgumentsResolver($this->mockContainer);
    $resolver->resolveCallableArguments([SampleController::class, 'staticMethod']);
  }

  public function testThrowsNotFoundExceptionIfTheTypeNotFoundInContainer(): void {
    $this->mockContainer
      ->expects($this->once())
      ->method('get')
      ->with(SampleService::class)
      ->willThrowException(new NotFoundException());

    $this->expectException(NotFoundExceptionInterface::class);

    $resolver = new TypeBasedArgumentsResolver($this->mockContainer);
    $resolver->resolveConstructorArguments(SampleController::class);
  }

  public function testUsesDefaultValueIfAvailable(): void {
    $resolver = new TypeBasedArgumentsResolver($this->mockContainer);
    $arguments = $resolver->resolveCallableArguments(function ($service = 'default value') {});

    $this->assertCount(1, $arguments);
    $this->assertSame('default value', $arguments[0]);
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
