<?php

namespace Headbanger\Tests;

use Mockery as m;
use PHPUnit_Framework_TestCase;
use Headbanger\Str;

class StrTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testFindSubstring()
    {
        $str = new Str('syaiful bahri');
        $this->assertEquals(8, $str->find('b'));
    }

    public function testStringIteration()
    {
        $str = new Str('Fòô Bàř', 'UTF-8');
        $resuls = [];
        foreach ($str as $char) {
            $resuls[] = $char;
        }
        $this->assertEquals(['F', 'ò', 'ô', ' ', 'B', 'à', 'ř'], $resuls);
    }

    public function testOffsetGet()
    {
        $str = new Str('Fòô', 'UTF-8');
        $this->assertEquals('F', $str->offsetGet(0));
        $this->assertEquals('ô', $str->offsetGet(2));
        $this->assertEquals('ô', $str[2]);
        $this->assertEquals('ô', $str[-1]);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testOffsetGetOutOfRangeException()
    {
        $str = new Str('Fòô', 'UTF-8');
        $a = $str[3];
    }

    /**
     *
     */
    public function testFindPositionSubstring()
    {
        $str = new Str('Fòô', 'UTF-8');
        $this->assertEquals(1, $str->find('ò'));
        $this->assertEquals(2, $str->rfind('ô'));
        $this->assertEquals(-1, $str->find('S'));
    }

    /**
     * @expectedException \Headbanger\Exceptions\ValueException
     */
    public function testGetIndexNonExistsSubstring()
    {
        $str = new Str('Bar');
        $str->index('S');
    }
}
