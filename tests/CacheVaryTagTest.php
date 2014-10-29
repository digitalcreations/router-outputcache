<?php

class CacheVaryTagTest extends PHPUnit_Framework_TestCase {
    function testSetContent() {
        $tag = new \DC\Router\OutputCache\Tag\CacheVaryTag("cache-vary", "foo");
        $this->assertEquals(["foo"], $tag->getParameters());
    }

    function testSetContentWithDollarSign() {
        $tag = new \DC\Router\OutputCache\Tag\CacheVaryTag("cache-vary", '$foo');
        $this->assertEquals(["foo"], $tag->getParameters());
    }

    function testSetContentWithWhitespace() {
        $tag = new \DC\Router\OutputCache\Tag\CacheVaryTag("cache-vary", ' $foo  bar');
        $this->assertEquals(["foo", "bar"], $tag->getParameters());
    }
}
 