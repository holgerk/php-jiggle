<?php

require_once __DIR__ . '/../lib/Jiggle.php';

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
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $this->assertEquals(42, $jiggle->d1);
    }

    public function testThatDepsCouldBeFactoryFunctions() {
        $jiggle = new Jiggle;
        $jiggle->d1 = function () {
            return 42;
        };
        $this->assertEquals(42, $jiggle->d1);
    }

    public function testThatDepsCouldBeWiredWithoutMagic() {
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $jiggle->d2 = function () use($jiggle) {
            return $jiggle->d1;
        };
        $this->assertEquals(42, $jiggle->d2);
    }

    public function testThatDepsCouldBeWiredWithMagicFactoryInjection() {
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $jiggle->d2 = function ($d1) {
            return $d1;
        };
        $this->assertEquals(42, $jiggle->d2);
    }

    public function testInstantiationWithoutMagic() {
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $jiggle->d3 = function () use($jiggle) {
            return new D3($jiggle->d1, $jiggle->d2);
        };
        $this->assertEquals(42, $jiggle->d3->sum());
    }

    public function testInstantiationWithMagicDepencyInjection() {
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $jiggle->d3 = function () use($jiggle) {
            return $jiggle->create('D3');
        };
        $this->assertEquals(42, $jiggle->d3->sum());
    }

    public function testInstantiationWithMagicDepencyInjectionShortForm() {
        $jiggle = new Jiggle;
        $jiggle->d1 = 40;
        $jiggle->d2 = 2;
        $jiggle->d3 = $jiggle->createFactory('D3');
        $this->assertEquals(42, $jiggle->d3->sum());
    }

    public function testInstantiationWithMagicDepencyInjectionAndNoDeps() {
        $jiggle = new Jiggle;
        $jiggle->d4 = function () use($jiggle) {
            return $jiggle->create('D4');
        };
        $this->assertInstanceOf('D4', $jiggle->d4);
    }

    public function testThatFactoriesCalledOnlyOnce() {
        $jiggle = new Jiggle;
        $callCount = 0;
        $jiggle->d1 = function () use(&$callCount) {
            $callCount++;
            return 42;
        };
        $this->assertEquals(42, $jiggle->d1);
        $this->assertEquals(42, $jiggle->d1);
        $this->assertEquals(1, $callCount);
    }

    public function testThatFactoriesCalledLayily() {
        $jiggle = new Jiggle;
        $called = false;
        $jiggle->d1 = function () use(&$called) {
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


}