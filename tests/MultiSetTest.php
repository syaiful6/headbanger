<?php

namespace Headbanger\Tests;

use Headbanger\Multiset;
use PHPUnit_Framework_TestCase;
use function Itertools\all;

class MultisetTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testMultisetValueAlwaysInteger()
    {
        $test = str_split('syaiful bahri');
        $Multiset = new Multiset($test);

        $this->assertTrue(all('is_integer', $Multiset->values()));
    }

    /**
     * Problem: our implementation of hashmap is unordered, if the elements count
     * have same value, the order is unpredictable.
     */
    public function testMultisetMostCommonOperation()
    {
        $Multiset = new Multiset(str_split('abracadabra'));
        $mostCommon = $Multiset->mostCommon(3);

        $this->assertCount(3, $mostCommon);
        $this->assertSame([['a', 5], ['b', 2], ['r', 2]], $mostCommon);
    }

    /**
     *
     */
    public function testMultisetElementOperation()
    {
        $Multiset = new Multiset(str_split('ABCABC'));
        $elements = iterator_to_array($Multiset->elements());

        $this->assertSame(['A', 'A', 'B', 'B', 'C', 'C'], $elements);
    }

    /**
     *
     */
    public function testMultisetNonMemberAlwaysReturnZero()
    {
        $Multiset = new Multiset(str_split('ABCABC'));

        $this->assertSame(0, $Multiset['d']);
    }

    /**
     *
     */
    public function testMultisetAddOperation()
    {
        $Multiset1 = new Multiset(str_split('aabbc'));
        $Multiset2 = new Multiset(str_split('abccc'));

        $added = $Multiset1->add($Multiset2);

        $this->assertTrue($added instanceof Multiset);
        $this->assertSame(3, $added['a']);
        $this->assertSame(4, $added['c']);
    }

    /**
     *
     */
    public function testMultisetSubstractOperation()
    {
        $Multiset1 = new Multiset(str_split('abbbc'));
        $Multiset2 = new Multiset(str_split('bccd'));

        $substracted = $Multiset1->substract($Multiset2);

        $this->assertTrue($substracted instanceof Multiset);
        $this->assertSame(1, $substracted['a']);
        $this->assertSame(2, $substracted['b']);
        $this->assertSame(0, $substracted['c']);
    }

    /**
     *
     */
    public function testMultisetUnionOperation()
    {
        $Multiset1 = new Multiset(str_split('abbb'));
        $Multiset2 = new Multiset(str_split('aab'));

        $union = $Multiset1->union($Multiset2);

        $this->assertTrue($union instanceof Multiset);
        $this->assertSame(2, $union['a']);
        $this->assertSame(3, $union['b']);
    }

    /**
     *
     */
    public function testMultisetIntersectionOperation()
    {
        $Multiset1 = new Multiset(str_split('abbb'));
        $Multiset2 = new Multiset(str_split('aab'));

        $intersect = $Multiset1->intersection($Multiset2);

        $this->assertTrue($intersect instanceof Multiset);
        $this->assertSame(1, $intersect['a']);
        $this->assertSame(1, $intersect['b']);
    }
}
