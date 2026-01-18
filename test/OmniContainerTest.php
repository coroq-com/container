<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Coroq\Container\OmniContainer;
use Coroq\Container\Exception\NotFoundException;
use Coroq\Container\Exception\CircularDependencyException;
use Coroq\Test\SampleClass;
use Coroq\Test\CircularA;

require_once __DIR__ . '/SampleClass.php';

/**
 * @covers Coroq\Container\OmniContainer
 */
class OmniContainerTest extends TestCase {
  public function testSetAndGetValue(): void {
    $container = new OmniContainer();
    $container->setValue('config', 'configuration value');

    $this->assertSame('configuration value', $container->get('config'));
  }

  public function testHasReturnsTrueForSetValue(): void {
    $container = new OmniContainer();
    $container->setValue('config', 'configuration value');

    $this->assertTrue($container->has('config'));
  }

  public function testHasReturnsFalseForNonExistentEntry(): void {
    $container = new OmniContainer();

    $this->assertFalse($container->has('non_existent_entry'));
  }

  public function testGetThrowsNotFoundException(): void {
    $container = new OmniContainer();

    $this->expectException(NotFoundException::class);
    $container->get('non_existent_entry');
  }

  public function testSetAndGetFactoryEntry(): void {
    $container = new OmniContainer();
    $factory = function () {
      return new stdClass();
    };

    $container->setFactory('service', $factory);

    $result = $container->get('service');
    $this->assertInstanceOf(stdClass::class, $result);
  }

  public function testSetAndGetSingletonFactoryEntry(): void {
    $container = new OmniContainer();
    $factory = function () {
      return new stdClass();
    };

    $container->setSingletonFactory('singleton_service', $factory);

    $firstInstance = $container->get('singleton_service');
    $secondInstance = $container->get('singleton_service');

    $this->assertSame($firstInstance, $secondInstance);
  }

  public function testSetAndGetClassEntry(): void {
    $container = new OmniContainer();
    $container->setClass('stdClass', stdClass::class);

    $result = $container->get('stdClass');
    $this->assertInstanceOf(stdClass::class, $result);
  }

  public function testSetAndGetSingletonClassEntry(): void {
    $container = new OmniContainer();
    $container->setSingletonClass('singleton_class', stdClass::class);

    $firstInstance = $container->get('singleton_class');
    $secondInstance = $container->get('singleton_class');

    $this->assertSame($firstInstance, $secondInstance);
  }

  public function testAddNamespaceAndGetDynamicEntry(): void {
    $container = new OmniContainer();
    $container->addNamespace('Coroq\\Test');

    $result = $container->get(SampleClass::class);
    $this->assertInstanceOf(SampleClass::class, $result);
  }

  public function testSetAndGetAliasEntry(): void {
    $container = new OmniContainer();

    $container->setValue('original_service', 'original value');
    $container->setAlias('alias_service', 'original_service');

    $result = $container->get('alias_service');

    $this->assertSame('original value', $result);
  }
  
  /**
   * Test that circular alias references are properly detected
   */
  public function testCircularAliasReferenceIsDetected(): void
  {
    $container = new OmniContainer();
    
    // Set up a circular reference between aliases
    $container->setAlias('service_a', 'service_b');
    $container->setAlias('service_b', 'service_c');
    $container->setAlias('service_c', 'service_a');
    
    // This should throw a CircularDependencyException
    $this->expectException(CircularDependencyException::class);
    $container->get('service_a');
  }
  
  /**
   * Test that aliases to non-existent entries are handled gracefully
   */
  public function testAliasToNonExistentEntryFailsGracefully(): void
  {
    $container = new OmniContainer();
    
    // Set an alias to a non-existent entry
    $container->setAlias('missing_service_alias', 'non_existent_service');
    
    // This should throw a NotFoundException
    $this->expectException(NotFoundException::class);
    $container->get('missing_service_alias');
  }
  
  /**
   * Test that self-referential aliases are detected
   */
  public function testSelfReferentialAliasIsDetected(): void
  {
    $container = new OmniContainer();
    
    // Set an alias that references itself
    $container->setAlias('self_reference', 'self_reference');
    
    // This should throw a CircularDependencyException
    $this->expectException(CircularDependencyException::class);
    $container->get('self_reference');
  }
  
  /**
   * Test that valid aliases still work correctly
   */
  public function testValidAliasChains(): void
  {
    $container = new OmniContainer();

    // Set up some valid services and aliases
    $container->setValue('actual_service', 'service value');
    $container->setAlias('service_alias', 'actual_service');
    $container->setAlias('alias_to_alias', 'service_alias');

    // These should all resolve to the original value
    $this->assertEquals('service value', $container->get('actual_service'));
    $this->assertEquals('service value', $container->get('service_alias'));
    $this->assertEquals('service value', $container->get('alias_to_alias'));
  }

  /**
   * Test that setRootContainer propagates to internal containers
   */
  public function testSetRootContainerPropagatesToChildren(): void
  {
    $container = new OmniContainer();

    // Create a mock root container that will provide a dependency
    $rootContainer = $this->createMock(\Psr\Container\ContainerInterface::class);
    $rootContainer->method('has')->willReturn(true);
    $rootContainer->method('get')->with(SampleClass::class)->willReturn(new SampleClass());

    $container->setRootContainer($rootContainer);

    // Register a factory that depends on SampleClass
    $container->setFactory('service', function (SampleClass $sample) {
      return $sample;
    });

    // The factory should resolve SampleClass from the root container
    $result = $container->get('service');
    $this->assertInstanceOf(SampleClass::class, $result);
  }

  /**
   * Test alias to DynamicContainer entry
   */
  public function testAliasResolvesToDynamicContainerEntry(): void
  {
    $container = new OmniContainer();
    $container->addNamespace('Coroq\\Test');
    $container->setAlias('sample', SampleClass::class);

    $result = $container->get('sample');
    $this->assertInstanceOf(SampleClass::class, $result);
  }

  /**
   * Test has() returns true for alias to existing entry
   */
  public function testHasReturnsTrueForAliasToExistingEntry(): void
  {
    $container = new OmniContainer();
    $container->setValue('actual', 'value');
    $container->setAlias('alias', 'actual');

    $this->assertTrue($container->has('alias'));
  }

  /**
   * Test has() returns false for alias to non-existent entry
   */
  public function testHasReturnsFalseForAliasToNonExistentEntry(): void
  {
    $container = new OmniContainer();
    $container->setAlias('alias', 'non_existent');

    $this->assertFalse($container->has('alias'));
  }

  /**
   * Test has() detects self-referential alias
   */
  public function testHasDetectsSelfReferentialAlias(): void
  {
    $container = new OmniContainer();
    $container->setAlias('self', 'self');

    $this->expectException(CircularDependencyException::class);
    $container->has('self');
  }

  /**
   * Test has() detects circular alias chain
   */
  public function testHasDetectsCircularAliasChain(): void
  {
    $container = new OmniContainer();
    $container->setAlias('a', 'b');
    $container->setAlias('b', 'c');
    $container->setAlias('c', 'a');

    $this->expectException(CircularDependencyException::class);
    $container->has('a');
  }

  /**
   * Test circular dependency via class constructor
   */
  public function testCircularDependencyViaClassConstructor(): void
  {
    $container = new OmniContainer();
    $container->addNamespace('Coroq\\Test');

    $this->expectException(CircularDependencyException::class);
    $container->get(CircularA::class);
  }

  /**
   * Test two-way circular alias
   */
  public function testTwoWayCircularAlias(): void
  {
    $container = new OmniContainer();
    $container->setAlias('ping', 'pong');
    $container->setAlias('pong', 'ping');

    $this->expectException(CircularDependencyException::class);
    $container->get('ping');
  }
}
