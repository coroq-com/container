<?php

namespace Coroq\Test {
  class SampleClass {
  }

  class SampleClassWithConstructor {
    public $a;
    public $b;
    public function __construct($a, $b) {
      $this->a = $a;
      $this->b = $b;
    }
  }

  class RecursiveClass {
    public function __construct(self $recursiveClass) {
    }
  }
}

namespace Coroq\Test2 {
  class SampleClass2 {
  }
}
