<?php

namespace Headbanger\Tests;

use PHPUnit_Framework_TestCase;
use Headbanger\ArrayList;
use Headbanger\Utils\Slice;

class ArrayListTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    protected function getInstance()
    {
        return new ArrayList();
    }

    /**
     *
     */
    public function testInitialListZero()
    {
        $list = $this->getInstance();
        $this->assertCount(0, $list);
        $this->assertTrue($list->isEmpty());
    }

    /**
     *
     */
    public function testAppendShouldIncreaseCount()
    {
        $list = $this->getInstance();
        $list->push('foo');
        $this->assertCount(1, $list);
        $list->push('bar');
        $this->assertCount(2, $list);
    }

    /**
     *
     */
    public function testAddingItemIsEmptyFalse()
    {
        $list = $this->getInstance();
        $list->push('foo');
        $this->assertFalse($list->isEmpty());
    }

    /**
     *
     */
    public function testClearCountZero()
    {
        $list = $this->getInstance();
        $list->push('foo');
        $list->clear();
        $this->assertCount(0, $list);
    }

    /**
     *
     */
    public function testIterationEmptyList()
    {
        $list = $this->getInstance();
        $results = [];
        foreach ($list as $el) {
            $results[] = $el;
        }

        $this->assertCount(0, $results);
    }

    /**
     *
     */
    public function testSliceLists()
    {
        $list = $this->getInstance();
        $list->extend([1, 2, 3, 4,5]);
        $slice = new Slice(0, 3);
        $slicing = $list[$slice];
        $this->assertCount(3, $slicing);
    }

    /**
     *
     */
    public function testGetItemNegativeIndices()
    {
        $list = $this->getInstance();
        $list->extend([1, 2, 3, 4]);
        $this->assertEquals(4, $list[-1]);
        $this->assertEquals(3, $list[-2]);
    }

    /**
     *
     */
    public function testClearList()
    {
        $list = $this->getInstance();
        $list->extend([1, 2, 3, 4]);
        $this->assertCount(4, $list);
        $list->clear();
        $this->assertCount(0, $list);
    }

    /**
     *
     */
    public function testPopItemList()
    {
        $list = $this->getInstance();
        $list->extend([1, 2, 3]);
        $this->assertEquals(3, $list->pop());
        $this->assertEquals(1, $list->pop(0));
        $this->assertCount(1, $list);
    }

    /**
     *
     */
    public function testReverseList()
    {
        $list = $this->getInstance();
        $list->extend([1, 2, 3]);
        $this->assertEquals(1, $list[0]);
        $list->reverse();
        $this->assertEquals(3, $list[0]);
        $this->assertEquals(2, $list[1]);
        $this->assertEquals(1, $list[2]);
    }

    /**
     *
     */
    public function testContainsList()
    {
        $list = $this->getInstance();
        $list->push('foo');
        $list->push('bar');
        $this->assertTrue($list->contains('foo'));
    }

    /**
     *
     */
    public function testGetIndexOfAnItem()
    {
        $list = $this->getInstance();
        $list->push('foo');
        $list->push('bar');
        $this->assertEquals(0, $list->index('foo'));
    }

    /**
     * @expectedException \Headbanger\Exceptions\ValueException
     */
    public function testGetIndexOfAnExistsItem()
    {
        $list = $this->getInstance();
        $list->push('foo');
        $list->index('baz');
    }

    /**
     * @expectedException UnderflowException
     */
    public function testExceptionRemoveEmptyList()
    {
        $list = $this->getInstance();
        $list->remove('anything');
    }

    /**
     * @expectedException \Headbanger\Exceptions\ValueException
     */
    public function testExceptionUnExistsItem()
    {
        $list = $this->getInstance();
        $list->push('foo');
        $list->remove('baz');
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testExceptionInvalidIndex()
    {
        $list = $this->getInstance();
        $list->extend([0, 1, 2]);
        $list[3]; // 3 is invalid
    }
}
