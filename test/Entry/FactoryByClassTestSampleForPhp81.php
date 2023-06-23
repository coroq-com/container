<?php

class FactoryByClassSampleArgument {
  public function __construct(public readonly string $value) {
  }
}

class FactoryByClassSampleWithConstructorWithDefaultObjectArgument {
  private $object;

  public function __construct($object = new FactoryByClassSampleArgument('TEST')) {
    $this->object = $object;
  }

  public function getObject() {
    return $this->object;
  }
}
