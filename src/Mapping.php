<?php

namespace Headbanger;

use Countable;
use ArrayAccess;
use IteratorAggregate;
use OutOfBoundsException;
use Headbanger\Exception\NotSupportedException;

abstract class Mapping implements Countable, ArrayAccess, IteratorAggregate
{
    /**
     * Get the mapping value by their key. if not exists return provided
     * default.
     *
     * @param mixed $key     The key to retrieve item on mapping
     * @param mixed $default The default value if the mapping not contains $key
     */
    public function get($key, $default = null)
    {
        if ($this->isEmpty()) {
            return $default;
        }

        try {
            return $this[$key];
        } catch (OutOfBoundsException $e) {
            return $default;
        }
    }

    /**
     * Test if this mapping contains an item (key).
     */
    public function contains($item)
    {
        return $this->offsetExists($item);
    }

    /**
     *
     */
    public function isEmpty()
    {
        return count($this) <= 0;
    }

    /**
     * Take an item in mapping by their key, return the item if exists, otherwise
     * throw \OutOfBoundsException
     *
     * @param  mixed                 $key The key of item in this mapping
     * @return mixed                 The item value
     * @throws \OutOfBoundsException if key doesn't exists
     */
    public function offsetGet($key)
    {
        throw new OutOfBoundsException();
    }

    /**
     *
     */
    public function offsetExists($key)
    {
        if ($this->isEmpty()) {
            return false;
        }
        try {
            $this[$key]; // raise OutOfBoundsException for non exists key

            return true;
        } catch (OutOfBoundsException $e) {
            // Expected, return false
            return false;
        }
    }

    /**
     *
     */
    public function values()
    {
        return new MappingValuesView($this);
    }

    /**
     *
     */
    public function keys()
    {
        return new MappingKeysView($this);
    }

    /**
     *
     */
    public function items()
    {
        return new MappingItemsView($this);
    }

     /**
     *
     */
    public function offsetUnset($key)
    {
        throw new NotSupportedException(sprintf(
                '%s does\'t support item deletion.',
                get_called_class()
            ));
    }

    /**
     *
     */
    public function offsetSet($key, $value)
    {
        throw new NotSupportedException(sprintf(
                '%s does\'t support item assigment.',
                get_called_class()
            ));
    }
}
