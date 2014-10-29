<?php

class TestCallableClass {
    /**
     * @cache
     */
    function cached() {}

    function uncached() {}

    /**
     * @cache
     */
    function oneParameter($x) {}

    /**
     * @cache
     */
    function twoParameter($x, $y) {}
}

class DefaultKeyGeneratorTest extends PHPUnit_Framework_TestCase {
    function testAnonymousFunctionWithoutParameters() {
        $generator = new \DC\Router\OutputCache\DefaultKeyGenerator();
        $key = $generator->fromCallableAndParams(function() {}, []);
        $this->assertStringStartsWith("dcoc_anon_", $key);
    }

    function testClassCallableWithoutParameters() {
        $generator = new \DC\Router\OutputCache\DefaultKeyGenerator();
        $key = $generator->fromCallableAndParams(['\TestCallableClass', 'cached'], []);
        $this->assertEquals("dcoc_TestCallableClass::cached", $key);
    }

    function testObjectCallableWithoutParameters() {
        $generator = new \DC\Router\OutputCache\DefaultKeyGenerator();
        $key = $generator->fromCallableAndParams([new \TestCallableClass, 'cached'], []);
        $this->assertEquals("dcoc_TestCallableClass::cached", $key);
    }

    function testObjectCallableWithParameter() {
        $generator = new \DC\Router\OutputCache\DefaultKeyGenerator();
        $key = $generator->fromCallableAndParams([new \TestCallableClass, 'oneParameter'], ["x" => 1]);
        $this->assertEquals("dcoc_TestCallableClass::oneParameter?x=1", $key);
    }

    function testObjectCallableWithTwoParameters() {
        $generator = new \DC\Router\OutputCache\DefaultKeyGenerator();
        $key = $generator->fromCallableAndParams([new \TestCallableClass, 'twoParameter'], ["x" => 1, "y" => 2]);
        $this->assertEquals("dcoc_TestCallableClass::twoParameter?x=1&y=2", $key);
    }
}
 