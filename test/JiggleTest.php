<?php

error_reporting(E_STRICT);

require_once __DIR__ . '/../src/Jiggle.php';

class D3 {
    public function __construct($d1, $d2) {
        $this->d1 = $d1;
        $this->d2 = $d2;
    }
    public function sum() {
        return $this->d1 + $this->d2;
    }
}

class D4 {}

class JiggleTest extends PHPUnit_Framework_TestCase {

    public function testThatSetDepsCouldBeGet() {
        // <example: Set and get dependencies
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $this->assertEquals(42, $jiggle->d1);
        // example>
    }

    public function testThatDepsCouldBeFactoryFunctions() {
        // <example: Lazy loading with factory functions
        $jiggle = new Jiggle;
        $jiggle->d1 = function() {
            return 42;
        };
        $this->assertEquals(42, $jiggle->d1);
        // example>
    }

    public function testThatDepsCouldBeWiredWithoutMagic() {
        // <example: Simple wiring of dependencies
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $jiggle->d2 = function() use($jiggle) {
            return $jiggle->d1;
        };
        $this->assertEquals(42, $jiggle->d2);
        // example>
    }

    public function testThatDepsCouldBeWiredWithMagicFactoryInjection() {
        // <example: Magic injection of depencies into factory functions
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $jiggle->d2 = function($d1) {
            return $d1;
        };
        $this->assertEquals(42, $jiggle->d2);
        // example>
    }

    public function testInstantiationWithoutMagic() {
        // <example: Simple instantiation
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $jiggle->d3 = function() use($jiggle) {
            return new D3($jiggle->d1, $jiggle->d2);
        };
        $this->assertEquals(42, $jiggle->d3->sum());
        // example>
    }

    public function testInstantiationWithMagicDepencyInjection() {
        // <example: Instantiation with magic constructor injection
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $jiggle->d3 = function() use($jiggle) {
            return $jiggle->create('D3');
        };
        $this->assertEquals(42, $jiggle->d3->sum());
        // example>
    }

    public function testInstantiationWithMagicDepencyInjectionShortForm() {
        // <example: Short form of magic constructor injection
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $jiggle->d3 = $jiggle->createFactory('D3');
        $this->assertEquals(42, $jiggle->d3->sum());
        // example>
    }

    public function testInstantiationWithMagicDepencyInjectionAndNoDeps() {
        $jiggle = new Jiggle;
        $jiggle->d4 = function() use($jiggle) {
            return $jiggle->create('D4');
        };
        $this->assertInstanceOf('D4', $jiggle->d4);
    }

    public function testThatFactoriesCalledOnlyOnce() {
        $jiggle = new Jiggle;
        $callCount = 0;
        $jiggle->d1 = function() use(&$callCount) {
            $callCount++;
            return 42;
        };
        $this->assertEquals(42, $jiggle->d1);
        $this->assertEquals(42, $jiggle->d1);
        $this->assertEquals(1, $callCount);
    }

    public function testThatFactoriesCalledLayzily() {
        $jiggle = new Jiggle;
        $called = false;
        $jiggle->d1 = function() use(&$called) {
            $called = true;
            return 42;
        };
        $this->assertFalse($called);
        $this->assertEquals(42, $jiggle->d1);
        $this->assertTrue($called);
    }

    public function testThatReferencesAreReturned() {
        $jiggle = new Jiggle;
        $jiggle->d1 = 21;
        $number = &$jiggle->d1;
        $number += 21;
        $this->assertEquals(42, $jiggle->d1);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Circular dependencies: a -> b -> c -> d -> b
     */
    public function testThatCircularDepsCauseAnException() {
        $jiggle = new Jiggle;
        $jiggle->a = function($b) { return $b; };
        $jiggle->b = function($c) { return $c; };
        $jiggle->c = function($d) { return $d; };
        $jiggle->d = function($b) { return $b; };
        $jiggle->a;
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Dependency is missing: a
     */
    public function testThatMissingDepCauseAnException() {
        $jiggle = new Jiggle;
        $jiggle->a;
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Dependency allready exists: a
     */
    public function testThatAlreadySetDepCauseAnException() {
        $jiggle = new Jiggle;
        $jiggle->a = true;
        $jiggle->a = false;
    }


}