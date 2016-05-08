<?php
namespace Headbanger\Tests;

use Mockery as m;
use PHPUnit_Framework_TestCase;
use Headbanger\Hashable;
use Headbanger\HashMap;

class HashMapTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testCountEmptyReturnsZero()
    {
        $map = new HashMap();
        $this->assertCount(0, $map);
    }

    /**
     *
     */
    public function testIsEmptyReturnTrue()
    {
        $map = new HashMap();
        $this->assertTrue($map->isEmpty());
    }

    /**
     *
     */
    public function testOffsetSetOffsetGet()
    {
        $map = new HashMap();
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
        $map = new HashMap();
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
    public function testSetAndGetKeyHashable()
    {
        $hashable = m::mock(Hashable::class)
            ->shouldReceive('hashCode')
            ->twice() // for setting and for getting
            ->andReturn(1)->mock();

        $map = new HashMap();
        $map[$hashable] = 'foo';
        $this->assertFalse($map->isEmpty());

        $this->assertEquals('foo', $map[$hashable]);
    }

     /**
     *
     */
    public function testSetAndContains()
    {
        $hashable = m::mock(Hashable::class)
            ->shouldReceive('hashCode')
            ->twice() // for setting and for getting
            ->andReturn(1)->mock();

        $map = new HashMap();
        $map[$hashable] = 'foo';
        $this->assertFalse($map->isEmpty());

        $this->assertTrue($map->contains($hashable));
    }

    /**
     *
     */
    public function testNonExistKeyHashable()
    {
        $hashable = m::mock(Hashable::class)
            ->shouldReceive('hashCode')
            ->once()
            ->andReturn(1)->mock();
        $map = new HashMap();
        // without this, HashMap immediately return false
        // because they check if it empty before search
        $map['foo'] = true;
        $this->assertFalse($map->contains($hashable));
    }

    /**
     *
     */
    public function testPopAndGet()
    {
        $map = new HashMap();
        $map['foo'] = 'bar';
        $map['baz'] = 'lorem';

        $this->assertEquals('bar', $map->pop('foo'));
        $this->assertFalse($map->contains('foo'));
        $this->assertCount(1, $map);

        $this->assertEquals('lorem', $map['baz']);
    }

    /**
     *
     */
    public function testGetMethod()
    {
        $map = new HashMap();
        $map['foo'] = 'bar';

        $this->assertEquals('bar', $map->get('foo'));
        $this->assertEquals('default', $map->get('nonexists', 'default'));
    }

    /**
     *
     */
    public function testIterationHashMapYieldKey()
    {
        $map = new HashMap();
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
        $map = new HashMap();
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
        $map = new HashMap();
        $map['foo'] = 'baz';
        $map['lorem'] = 'ipsum';
        foreach ($map as $k) {
            $map[$k] = 'replaced';
        }
        $this->assertCount(2, $map);
        $this->assertEquals('replaced', $map['foo']);
        $this->assertEquals('replaced', $map['lorem']);
    }

    /**
     *
     */
    public function testPopItemMap()
    {
        $map = new HashMap();
        $map['lorem'] = 'ipsum';
        $this->assertEquals(['lorem', 'ipsum'], $map->popItem());
    }

    /**
     * @expectedException UnderflowException
     */
    public function testPopItemOnEmptyMap()
    {
        $map = new HashMap();
        $map->popItem();
    }

    /**
     *
     */
    public function testPopMethodOnMapping()
    {
        $map = new HashMap();
        $map['foo'] = 'bar';
        $this->assertEquals('bar', $map->pop('foo'));
        $this->assertCount(0, $map);
        $this->assertEquals('default', $map->pop('any', 'default'));
    }
}
