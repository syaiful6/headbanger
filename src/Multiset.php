<?php

namespace Headbanger;

use UnderflowException;
use OutOfBoundsException;
use function Itertools\sort_by;
use function Itertools\slice;
use function Itertools\splat_map;
use function Itertools\chain_from_iterable;

/**
 * Implementation of multiset. It's used for counting element. Elements are
 * stored as keys and their counts are stored as values. It's also
 * implements Multiset-style mathematical operations. Reference:
 * - Knuth TAOCP Volume II section 4.6.3 exercise 19
 * - http://en.wikipedia.org/wiki/Multiset
 * Output of mathematical operation guaranteed to only include positive counts.
 */
class Multiset extends HashMap
{
    /**
     *
     */
    public function __construct($iterable = null)
    {
        parent::__construct();
        $this->update($iterable);
    }

    /**
     * Return multi dimensional array for $n most common elements and their
     * counts from the most to the least. If $n is null, then it return all
     * element counts.
     *
     * @param  int $n
     * @return array
     */
    public function mostCommon($n = null)
    {
        $list = sort_by($this->items(), function ($elem) {
            return -$elem[1];
        });

        if ($n !== null) {
            return iterator_to_array(slice($list, 0, $n));
        }

        return $list;
    }

    /**
     * Return an Iterator over elements repeating each as many times as its count.
     * For now the Iterator returned by this function is Generator, it may be
     * change in the future. It best you treat it as an iterator.
     *
     * @return \Iterator The current implementation return Generator
     */
    public function elements()
    {
        $iter = splat_map('\\Itertools\\repeat', $this->items());

        return chain_from_iterable($iter);
    }

    /**
     *
     */
    public function add(Multiset $other)
    {
        $result = new static();
        // set with elements from both this and other
        $bothMember = (new Set($this))->union(new Set($other));
        foreach ($bothMember as $elem) {
            $newcount = $this[$elem] + $other[$elem];
            if ($newcount > 0) {
                $result[$elem] = $newcount;
            }
        }

        return $result;

        return $result;
    }

    /**
     * Subtract count, but keep only results with positive counts.
     */
    public function substract(Multiset $other)
    {
        $result = new static();
        // set with elements from both this and other
        $bothMember = (new Set($this))->union(new Set($other));
        foreach ($bothMember as $elem) {
            $newcount = $this[$elem] - $other[$elem];
            if ($newcount > 0) {
                $result[$elem] = $newcount;
            }
        }

        return $result;
    }

    /**
     * Union is the maximum of value in either of the input Multisets.
     */
    public function union(Multiset $other)
    {
        $result = new static();
        $bothMember = (new Set($this))->union($other);
        foreach ($bothMember as $elem) {
            $newcount = max($this[$elem], $other[$elem]);
            if ($newcount > 0) {
                $result[$elem] = $newcount;
            }
        }

        return $result;
    }

    /**
     * Intersection is the minimum of corresponding counts.
     */
    public function intersection(Multiset $other)
    {
        $result = new static();
        $bothMember = (new Set($this))->union($other);
        foreach ($bothMember as $elem) {
            $newcount = min($this[$elem], $other[$elem]);
            if ($newcount > 0) {
                $result[$elem] = $newcount;
            }
        }

        return $result;
    }

    /**
     * Called by hashmap if they cant find the key. Because this is multiset, then
     * non existing value always 0.
     */
    protected function offsetMissing($key)
    {
        return 0;
    }

    /**
     * Unset the item at a given offset.
     *
     * @param mixed $key
     */
    public function offsetUnset($key)
    {
        try {
            parent::offsetUnset($key);
        } catch (UnderflowException $e) {
            // pass, the Multiset is empty
        } catch (OutOfBoundsException $e) {
            // the Multiset is not have that value. no need to scream here.
        }
    }

    /**
     *
     */
    public function update($iterable)
    {
        if ($iterable !== null) {
            if ($iterable instanceof Mapping) {
                if (count($this) > 0) {
                    foreach ($iterable->items() as list($elem, $count)) {
                        $this[$elem] = $count + $this->get($elem, 0);
                    }
                } else {
                    parent::update($iterable);
                }
            } else {
                foreach ($iterable as $elem) {
                    $this[$elem] = $this->get($elem, 0) + 1;
                }
            }
        }
    }

    /**
     *
     */
    public static function fromKeys($keys, $value = 0)
    {
        throw new \RuntimeException(
            'cant create Multiset fromkey, use new Multiset(iterable) instead.'
        );
    }
}
