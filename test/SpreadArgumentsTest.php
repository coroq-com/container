<?php
declare(strict_types=1);
use Coroq\Container\SpreadArguments;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class SpreadArgumentsTest extends TestCase {
  public function testSpreadArgumentsWithNoArgs() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $callable = function () {
      return 'ok';
    };
    $spreadArguments = new SpreadArguments($callable);
    $result = $spreadArguments($containerMock);
    $this->assertEquals('ok', $result);
  }

  public function testSpreadArgumentsWithSingleArg() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->expects($this->once())
      ->method('get')
      ->with('config')
      ->willReturn('my_config_value');
    $callable = function ($config) {
      return $config;
    };
    $spreadArguments = new SpreadArguments($callable);
    $result = $spreadArguments($containerMock);
    $this->assertEquals('my_config_value', $result);
  }

  public function testSpreadArgumentsWithMultipleArgs() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->expects($this->exactly(2))
      ->method('get')
      ->withConsecutive(['config'], ['myService'])
      ->willReturnOnConsecutiveCalls('my_config_value', 'my_service_value');
    $callable = function ($config, $myService) {
      return $config . ', ' . $myService;
    };
    $spreadArguments = new SpreadArguments($callable);
    $result = $spreadArguments($containerMock);
    $this->assertEquals('my_config_value, my_service_value', $result);
  }

  public function testSpreadArgumentsWithFunctionName() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->expects($this->once())
      ->method('get')
      ->with('config')
      ->willReturn('my_config_value');
    function testFunction($config) {
      return $config;
    }
    $spreadArguments = new SpreadArguments('testFunction');
    $result = $spreadArguments($containerMock);
    $this->assertEquals('my_config_value', $result);
  }

  public function testSpreadArgumentsWithObjectMethod() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->expects($this->once())
      ->method('get')
      ->with('config')
      ->willReturn('my_config_value');
    $object = new class {
      public function testMethod($config) {
        return $config;
      }
    };
    $spreadArguments = new SpreadArguments([$object, 'testMethod']);
    $result = $spreadArguments($containerMock);
    $this->assertEquals('my_config_value', $result);
  }

  public function testSpreadArgumentsWithStaticMethod() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->expects($this->once())
      ->method('get')
      ->with('config')
      ->willReturn('my_config_value');
    $spreadArguments = new SpreadArguments([SpreadArgumentsTestHelper::class, 'staticTestMethod']);
    $result = $spreadArguments($containerMock);
    $this->assertEquals('my_config_value', $result);
  }

  public function testSpreadArgumentsWithStaticMethodString() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->expects($this->once())
      ->method('get')
      ->with('config')
      ->willReturn('my_config_value');
    $spreadArguments = new SpreadArguments('SpreadArgumentsTestHelper::staticTestMethod');
    $result = $spreadArguments($containerMock);
    $this->assertEquals('my_config_value', $result);
  }

  public function testSpreadArgumentsWithInvokableObject() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $containerMock
      ->expects($this->once())
      ->method('get')
      ->with('config')
      ->willReturn('my_config_value');
    $invokable = new class {
      public function __invoke($config) {
        return $config;
      }
    };
    $spreadArguments = new SpreadArguments($invokable);
    $result = $spreadArguments($containerMock);
    $this->assertEquals('my_config_value', $result);
  }

  public function testSpreadArgumentsWithContainerName() {
    $containerMock = $this->createMock(ContainerInterface::class);
    $closure = function ($__container) {
      return $__container;
    };
    $spreadArguments = new SpreadArguments($closure);
    $result = $spreadArguments($containerMock);
    $this->assertSame($containerMock, $result);
  }
}

class SpreadArgumentsTestHelper {
  public static function staticTestMethod($config) {
    return $config;
  }
}
