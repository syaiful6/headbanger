<?php
namespace Headbanger\Tests;

use Mockery as m;
use PHPUnit_Framework_TestCase;
use Headbanger\Hashable;
use Headbanger\HashTable;

class HashtableTest extends PHPUnit_Framework_TestCase
{
    public function testCountEmptyReturnsZero() {
        $map = new HashTable();
        $this->assertCount(0, $map);
    }

    /**
     *
     */
    public function testIsEmptyReturnTrue()
    {
        $map = new HashTable();
        $this->assertTrue($map->isEmpty());
    }

    /**
     *
     */
    public function testOffsetSetOffsetGet()
    {
        $map = new HashTable();
        $this->assertCount(0, $map);
        $this->assertTrue($map->isEmpty());

        $map['foo'] = 'baz';
        $this->assertEquals('baz', $map['foo']);
        $this->assertFalse($map->isEmpty());
        $this->assertCount(1, $map);

        $map['lorem'] = 'ipsum';
        $this->assertEquals('ipsum', $map['lorem']);
        $this->assertCount(2, $map);
        $this->assertFalse($map->isEmpty());

        // try replace
        $map['foo'] = 'bar';
        $this->assertEquals('bar', $map['foo']);
        $this->assertCount(2, $map);
        $this->assertFalse($map->isEmpty());

    }

    /**
     *
     */
    public function testOffsetUnset()
    {
        $map = new HashTable();
        $map[0] = 1;
        $this->assertCount(1, $map);
        $this->assertFalse($map->isEmpty());
        unset($map[0]);
        $this->assertCount(0, $map, 'After unsetting count should decrese');
        $this->assertTrue($map->isEmpty());
    }

    /**
     *
     */
    public function testIterationHashtableYieldKey()
    {
        $map = new HashTable();
        $map['foo'] = 'baz';
        $map['lorem'] = 'ipsum';
        // our hastable is unordered, so just test the count
        // followed by the member
        $keys = [];
        foreach ($map as $elem) {
            $keys[] = $elem;
        }
        $this->assertCount(2, $keys);
        $this->assertTrue(in_array('foo', $keys));
        $this->assertTrue(in_array('lorem', $keys));
    }

    /**
     *
     */
    public function testHashIteration()
    {
        $map = new HashTable();
        $map['foo'] = 'baz';
        $map['lorem'] = 'ipsum';

        $copy = [];
        foreach ($map as $e) {
            $copy[] = $e;
        }
        $this->assertCount(2, $copy);
    }

    /**
     *
     */
    public function testReplaceDuringIteration()
    {
        $map = new HashTable();
        $map['foo'] = 'baz';
        $map['lorem'] = 'ipsum';
        foreach ($map->keys() as $k) {
            $map[$k] = 'replaced';
        }
        $this->assertCount(2, $map);
        $this->assertEquals('replaced', $map['foo']);
        $this->assertEquals('replaced', $map['lorem']);
    }
}
