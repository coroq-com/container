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

  class CircularA {
    public function __construct(CircularB $b) {
    }
  }

  class CircularB {
    public function __construct(CircularA $a) {
    }
  }
}

namespace Coroq\Test2 {
  class SampleClass2 {
  }
}
