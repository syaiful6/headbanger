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
        $list->append('foo');
        $this->assertCount(1, $list);
        $list->append('bar');
        $this->assertCount(2, $list);
    }

    /**
     *
     */
    public function testAddingItemIsEmptyFalse()
    {
        $list = $this->getInstance();
        $list->append('foo');
        $this->assertFalse($list->isEmpty());
    }

    /**
     *
     */
    public function testClearCountZero()
    {
        $list = $this->getInstance();
        $list->append('foo');
        $list->clear();
        $this->assertCount(0, $list);
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
        $list->append('foo');
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
