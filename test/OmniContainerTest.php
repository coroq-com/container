<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Coroq\Container\OmniContainer;
use Coroq\Container\Exception\NotFoundException;
use Coroq\Container\Exception\CircularDependencyException;
use Coroq\Test\SampleClass;

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
}
