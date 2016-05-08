<?php
namespace Headbanger\Tests;

use PHPUnit_Framework_TestCase;
use Headbanger\Set;

class SetTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testCountable()
    {
        $s1 = new Set([1, 2]);
        $s2 = new Set([1, 2, 3]);

        $this->assertCount(2, $s1);
        $this->assertCount(3, $s2);
    }

    /**
     *
     */
    public function testIsSubsetOperation()
    {
        $s1 = new Set([1, 2]);
        $s2 = new Set([1, 2, 3]);

        $this->assertTrue($s1->isSubset($s2));
        $this->assertFalse($s2->isSubset($s1));
    }

    /**
     *
     */
    public function testIsSupersetOperation()
    {
        $s1 = new Set([1, 2, 3]);
        $s2 = new Set([1, 2]);

        $this->assertTrue($s1->isSuperset($s2));
        $this->assertFalse($s2->isSuperset($s1));
    }
}
