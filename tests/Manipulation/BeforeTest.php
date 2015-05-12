<?php

namespace DOMWrap\Tests\Manipulation;

class BeforeTest extends \PHPUnit_Framework_TestCase
{
    use \DOMWrap\Tests\Harness\TestTrait;

    public function testBeforeNode() {
        $expected = $this->document('<html><div class="inserted"></div><div class="test"></div></html>');

        $doc = $this->document('<html><div class="test"></div></html>');
        $nodes = $doc->find('.test');
        $nodes->first()->before('<div class="inserted"></div>');

        $this->assertEqualXMLStructure($expected->firstChild, $doc->firstChild);
    }

    public function testBeforeNodeList() {
        $expected = $this->document('<html><div class="inserted"></div><div class="test"></div><div class="inserted"></div><div class="test"></div></html>');

        $doc = $this->document('<html><div class="test"></div><div class="test"></div></html>');
        $nodes = $doc->find('.test');
        $nodes->before('<div class="inserted"></div>');

        $this->assertEqualXMLStructure($expected->firstChild, $doc->firstChild);
    }

    public function testBeforeNodeListNested() {
        $expected = $this->document('<html><article><div class="inserted"></div><div class="test"></div></article><div class="inserted"></div><a class="test"></a></html>');

        $doc = $this->document('<html><article><div class="test"></div></article><a class="test"></a></html>');
        $nodes = $doc->find('.test');
        $nodes->before('<div class="inserted"></div>');

        $this->assertEqualXMLStructure($expected->firstChild, $doc->firstChild);
    }
}