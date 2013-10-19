<?php

require_once __DIR__ . '/../lib/Jiggle.php';

class JiggleTest extends PHPUnit_Framework_TestCase {

    public function testThatSetDepsCouldBeGet() {
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $this->assertEquals(42, $jiggle->d1);
    }

    public function testThatDepsCouldBeCreatedViaFactoriesLazily() {
        $jiggle = new Jiggle;
        $jiggle->d1 = function () {
            static $callCount;
            if (is_null($callCount)) {
                $callCount = 0;
            }
            $callCount++;
            if ($callCount > 1) {
                throw new Exception("Unexpected second call");
            }
            return 42;
        };
        $this->assertEquals(42, $jiggle->d1);
        $this->assertEquals(42, $jiggle->d1);
    }

    public function testThatReferencesAreReturned() {
        $jiggle = new Jiggle;
        $jiggle->d1 = 21;
        $number = &$jiggle->d1;
        $number += 21;
        $this->assertEquals(42, $jiggle->d1);
    }

    public function testThatDepsAreInjectedIntoFactoryFunctions() {
        $jiggle = new Jiggle;
        $jiggle->d1 = 42;
        $jiggle->d2 = function ($d1) {
            return $d1;
        };
        $this->assertEquals(42, $jiggle->d2);
    }


}