<?php
namespace Headbanger\Tests;

use Mockery as m;
use PHPUnit_Framework_TestCase;
use Headbanger\ArrayList;

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
    public function testGetItemNegativeIndices()
    {
        $list = $this->getInstance();
        $list->extend([1,2,3,4]);
        $this->assertEquals(4, $list[-1]);
        $this->assertEquals(3, $list[-2]);
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
        $list->extend([0,1,2]);
        $list[3]; // 3 is invalid
    }
}
