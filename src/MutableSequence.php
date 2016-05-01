<?php
namespace Headbanger;

use OutofRangeException;
use UnderflowException;

abstract class MutableSequence extends Sequence
{
    /**
     * insert value before index
     */
    abstract public function insert($index, $value);

    /**
     *
     */
    public function append($value)
    {
        $this->insert(count($this), $value);
    }

    /**
     * This maybe slow operation but it effective, override this if there are
     * any shortcut for it
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
     *
     */
    public function extend($iterable)
    {
        foreach ($iterable as $el) {
            $this->append($el);
        }
    }

    /**
     *
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
