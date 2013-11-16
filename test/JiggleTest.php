<?php

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

    public function testThatDepsCouldBeWiredWithoutInjection() {
        // <example: Basic wiring of dependencies
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $jiggle->d2 = function() use($jiggle) {
            return $jiggle->d1;
        };
        $this->assertEquals(42, $jiggle->d2);
        // example>
    }

    public function testThatDepsCouldBeWiredWithImplicitSingletonFactoryInjection() {
        // <example: Implicit injection of depencies into singleton factory functions
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $jiggle->d2 = function($d1) {
            return $d1;
        };
        $this->assertEquals(42, $jiggle->d2);
        // example>
    }

    public function testThatDepsCanBeInjectedWithExplicitInjection() {
        // <example: Explicit injection of depencies into any function
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $result = $jiggle->inject(function($d1, $d2) {
            return $d1 + $d2;
        });
        $this->assertEquals(42, $result);
        // example>
    }

    public function testThatDepsCanBeInjectedWithExplicitInjectionWithOverloadOfDeps() {
        // <example: Explicit injection with dependency overloading
        $jiggle = new Jiggle;
        $jiggle->d1 = 20;
        $jiggle->d2 = 1000;
        $result = $jiggle->inject(function($d1, $d2, $d3) {
            return $d1 + $d2 + $d3;
        }, array('d2' => 20, 'd3' => 2));
        $this->assertEquals(42, $result);
        // example>
    }

    public function testBasicInstantiation() {
        // <example: Basic instantiation
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $jiggle->d3 = function() use($jiggle) {
            return new D3($jiggle->d1, $jiggle->d2);
        };
        $this->assertEquals(42, $jiggle->d3->sum());
        // example>
    }

    public function testInstantiationWithImplicitDepencyInjection() {
        // <example: Instantiation with implicit constructor injection
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $jiggle->d3 = function() use($jiggle) {
            return $jiggle->create('D3');
        };
        $this->assertEquals(42, $jiggle->d3->sum());
        // example>
    }

    public function testInstantiationWithImplicitDepencyInjectionShortForm() {
        // <example: Short form of implicit constructor injection
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $jiggle->d3 = $jiggle->singleton('D3');
        $this->assertEquals(42, $jiggle->d3->sum());
        // example>
    }

    public function testThatFunctionDepsCouldCalled() {
        // <example: Basic function dependency
        $jiggle = new Jiggle;
        $jiggle->d1 = function() {
            return function() {
                return 42;
            };
        };
        $this->assertEquals(42, $jiggle->d1());
        // example>
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Dependency allready exists: a
     */
    public function testThatAlreadySetDepCauseAnException() {
        // <example: Existing deps could not replaced by accident
        $jiggle = new Jiggle;
        $jiggle->a = true;
        $jiggle->a = false; // <- throws an exception
        // example>
    }

    public function testThatDepsCouldBeReplaced() {
        // <example: Existing deps could be replaced implicitly
        $jiggle = new Jiggle;
        $jiggle->d1 = 21;
        $jiggle->replace('d1', 42);
        $this->assertEquals(42, $jiggle->d1);
        // example>
    }

    public function testThatContainerSupportsIsset() {
        // <example: Isset support
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $this->assertTrue(isset($jiggle->d1));
        $this->assertFalse(isset($jiggle->d2));
        // example>
    }

    public function testThatResolverCouldProvidedWhichIsIvokedToResolveUnresolvableDeps() {
        // <example: Resolver is called for unresolvable deps
        $jiggle = new Jiggle;
        $jiggle->resolver(function($dependencyName) {
            if ($dependencyName == 'd2') {
                return 42;
            }
            throw new Exception("Could not resolve dependency: $dependencyName!");
        });
        $jiggle->d1 = function($d2) { return $d2; };
        $this->assertEquals(42, $jiggle->d1);
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

    public function testThatCircularDepencyMissdetectionIsFixed() {
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $jiggle->d3 = $jiggle->singleton('D3');
        $jiggle->d3;
        $jiggle->d3;
        $jiggle->d3;
    }


}