<?php
namespace Headbanger;

use Countable;
use ArrayAccess;
use IteratorAggregate;
use UnderflowException;
use OutOfRangeException;
use Headbanger\Exceptions\ValueException;

abstract class Sequence implements Countable, ArrayAccess, IteratorAggregate
{
    /**
     * @throws \OutOfRangeException if trying to get illegal index
     */
    public function offsetGet($index)
    {
        throw new OutOfRangeException(sprintf('illegal index %d', $index));
    }

    /**
     *
     */
    public function isEmpty()
    {
        return count($this) === 0;
    }

    /**
     * Check if this sequence contains for given elem
     */
    public function contains($value)
    {
        foreach ($this as $v) {
            if ($v === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the first index of the given value
     */
    public function index($value)
    {
        for ($i = 0, $len = count($this); $i < $len; $i++) {
            $v = $this[$i];
            if ($v === $value) {
                return $i;
            }
        }
        throw new ValueException('value not found!');
    }

    /**
     * IteratorAggregate implementation.
     */
    public function getIterator()
    {
        $i = 0;
        // try until
        try {
            while (true) {
                yield $this[$i];
                $i++;
            }
        } catch (OutOfRangeException $e) {
            // we already dont have item
        }
    }

    /**
     *
     */
    public function countValue($value)
    {
        $i = 0;
        foreach ($this as $v) {
            if ($v === $value) {
                $i += 1;
            }
        }
        return $i;
    }

    /**
     *
     */
    protected function intGuard($int)
    {
        if (filter_var($int, FILTER_VALIDATE_INT) === false) {
            throw new \InvalidArgumentException(sprintf(
                'offset must be integer'
            ));
        }
        return (int) $int;
    }

    /**
     * Calculate offset provided to work with negative index. If offset is negative
     * add them with the length of this container.
     */
    protected function calculateOffset($offset)
    {
        $offset = $this->intGuard($offset);
        if ($offset < 0) {
            $offset = count($this) + $offset;
        }

        return $offset;
    }
}
