<?php

namespace Headbanger;

use OutofRangeException;
use UnderflowException;

abstract class MutableSequence extends Sequence
{
    /**
     * insert value before index
     *
     * @param  integer $index
     * @param  mixed   $value
     * @return void
     */
    abstract public function insert($index, $value);

    /**
     * Push one element onto the end of sequence
     *
     * @param  mixed $value
     * @return void
     */
    public function push($value)
    {
        $this->insert(count($this), $value);
    }

    /**
     * Clear the sequence. This maybe slow operation but it effective,
     * override this if there are any shortcut for it.
     *
     * @return void
     */
    public function clear()
    {
        try {
            while (true) {
                $this->pop();
            }
        } catch (UnderflowException $e) {
            // pass underflow because it's mean empty
        }
    }

    /**
     * reverse this sequence in place
     *
     * @return void
     */
    public function reverse()
    {
        $n = count($this);
        $x = floor($n / 2) - 1; // the end of indices
        foreach (range(0, $x) as $i) {
            list($this[$i], $this[$n-$i-1]) = [$this[$n-$i-1], $this[$i]];
        }
    }

    /**
     * Extend the iterable
     *
     * @param  \Traversable|array $iterable
     * @return void
     */
    public function extend($iterable)
    {
        foreach ($iterable as $el) {
            $this->push($el);
        }
    }

    /**
     * retrieves the item at i and also removes it from sequence
     * default to -1, it's mean the default retrieve the last element and remove
     * it, change to 0 if you want to make effect like array_shift
     *
     * @param  integer $i
     * @return mixed
     */
    public function pop($i = -1)
    {
        if ($this->isEmpty()) {
            throw new UnderflowException(
                "trying to pop element on an empty sequence"
            );
        }
        $value = $this[$i];
        unset($this[$i]); // this will raise OutofRangeException

        return $value;
    }

     /**
     *
     */
    public function remove($value)
    {
        if ($this->isEmpty()) {
            throw new UnderflowException(
                "trying to pop element on an empty sequence"
            );
        }
        unset($this[$this->index($value)]);
    }

    /**
     * Must be overriden on child class
     *
     */
    public function offsetSet($index, $value)
    {
        throw new OutofRangeException();
    }

    /**
     *
     */
    public function offsetUnset($index)
    {
        throw new OutofRangeException();
    }
}
