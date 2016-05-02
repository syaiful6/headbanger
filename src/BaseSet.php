<?php

namespace Headbanger;

use Countable;
use IteratorAggregate;
use RuntimeException;
/**
 * This class provides common set operation like unions, difference,
 * and membership testing. Subclass only need to implements an abstract function
 * contains, count and getIterator
 */
abstract class BaseSet implements Countable, IteratorAggregate
{
    /**
     * @param  \Traversable|array $iterable
     * @return self  The new instance current class
     */
    protected static function fromIterable($iterable)
    {
        return new static($iterable);
    }

    /**
     * Determine a given item exists in sets, pass them to the storage
     *
     * @param  mixed   $elem
     * @return boolean
     */
    abstract public function contains($elem);

    /**
     * Test whether every element in the set is in other.
     *
     * @param mixed $other
     */
    public function isSubset($other)
    {
        $this->_sanityCheck($other, __METHOD__);
        if (count($this) > count($other)) {
            return false;
        }
        foreach ($this as $el) {
            if (! $other->contains($el)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Test whether the set is a proper subset of other. In other words
     * set <= other and set != other
     */
    public function isProperSubset($other)
    {
        $this->_sanityCheck($other, __METHOD__);
        return count($this) < count($other) && $this->isSubset($other);
    }

    /**
     * Test whether every element in other is in the set.
     */
    public function isSuperset($other)
    {
        $this->_sanityCheck($other, __METHOD__);
        return count($this) < count($other) && $this->isProperSuperset($other);
    }

    /**
     * Test whether the set is a proper superset of other
     */
    public function isProperSuperset($other)
    {
        $this->_sanityCheck($other, __METHOD__);
        if (count($this) < count($other)) {
            return false;
        }

        foreach ($other as $el) {
            if (! $this->contains($el)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return True if two sets have a null intersection.
     */
    public function isDisjoint($other)
    {
        foreach ($other as $el) {
            if ($this->contains($el)) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     */
     public function equals($other)
     {
         return count($this) === count($other) && $this->isSubset($other);
     }

    /**
     *
     */
    public function union($other)
    {
        $chain = function () use ($other) {
            foreach ([$this, $other] as $set) {
                foreach ($set as $s) {
                    yield $s;
                }
            }
        };
        return static::fromIterable($chain());
    }

    /**
     *
     */
    public function difference($other)
    {
        if (! $other instanceof BaseSet) {
            if (! $other instanceof \Traversable) {
                throw new RuntimeException(sprintf(
                    'parameter 1 passed to %s must be instance of %s or Traversable',
                    __METHOD__,
                    BaseSet::class
                ));
            }
            $other = static::fromIterable($other);
        }

        return static::fromIterable(call_user_func(function () use ($other) {
            foreach ($this as $value) {
                if (! $other->contains($value)) {
                    yield $value;
                }
            }
        }));
    }

    /**
     *
     */
    public function symmetricDifference($other)
    {
        if (! $other instanceof BaseSet) {
            if (! $other instanceof \Traversable) {
                throw new RuntimeException(sprintf(
                    'parameter 1 passed to %s must be instance of %s or Traversable',
                    __METHOD__,
                    BaseSet::class
                ));
            }
            $other = static::fromIterable($other);
        }
        $diff = $this->difference($other);
        $diff2 = $other->difference($this);

        return $diff->union($diff2);
    }

    /**
     * Internal routine to check if other is instance of this class
     *
     */
    private function _sanityCheck($other, $method)
    {
        if (! $other instanceof BaseSet) {
            throw new RuntimeException(sprintf(
                'parameter 1 passed to %s must be instance of %s',
                $method,
                BaseSet::class
            ));
        }
    }
}
