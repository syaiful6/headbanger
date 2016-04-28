<?php
namespace Headbanger;

use Countable;
use ArrayAccess;
use IteratorAggregate;
/**
 *
 */
abstract class BaseSet implements Countable, ArrayAccess, IteratorAggregate
{
    /**
     * @param HashTable
     */
    protected $members;

    /**
     * @param  mixed $iterable Any PHP object that can be converted as iterator
     *                         by itertools toIter function
     * @return self  The new instance current class
     */
    protected static function fromIterable($iterable)
    {
        return new static($iterable);
    }

    /**
     * Return the number of elements of a set.
     *
     * @return integer The count items in set
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Determine a given item exists in sets, pass them to the storage
     *
     * @param  mixed   $elem
     * @return boolean
     */
    public function contains($elem)
    {
        return $this->data->contains($elem);
    }

    /**
     *
     */
    public function isSubset(BaseSet $other)
    {

    }

    /**
     *
     */
    public function union(BaseSet $other)
    {
        $result = static::fromIterable($this);
        $result->_update($other);

        return $result;
    }

    /**
     * Reset the members of set to empty
     */
    protected function resetMembers($data)
    {
        $this->members = new HashTable();
    }
}
