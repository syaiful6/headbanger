<?php

namespace Headbanger;

use SplFixedArray;
use OutOfRangeException;
use Headbanger\Utils\Slice;

class ArrayList extends MutableSequence
{
    /**
     * The size of this sequence
     *
     * @var integer
     */
    protected $size = 0;

    /**
     * The undelying storage used
     *
     * @var \SplFixedArray
     */
    protected $items;

    /**
     * Construct ArrayList, optionally give it initial value
     *
     * @param \Traversable|array
     * @return void
     */
    public function __construct($iterable = null)
    {
        $this->items = new SplFixedArray(0); // just initialize with 0 capacity
        if ($iterable !== null) {
            $this->extend($iterable);
        }
    }

    /**
     * Clear all item on list
     *
     * @return void
     */
    public function clear()
    {
        $this->items = new SplFixedArray(0);
        $this->size = 0;
    }

    /**
     * Count the number of items
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return integer
     */
    public function count()
    {
        return $this->size;
    }

    /**
     * insert value before index
     *
     * @param  integer $index
     * @param  mixed   $value
     * @return void
     */
    public function insert($index, $value)
    {
        $slice = new Slice($index, $index);
        $this[$slice] = [$value];
    }

    /**
     * Assign a value to the specified offset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param  mixed $index
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($index, $value)
    {
        if (! $index instanceof Slice) {
            $index = $this->guardedSeek($index, __METHOD__);
            $this->insertWhere($index, $value);

            return;
        }
        // slice indices
        list($start, $stop, $step) = $index->indices(count($this));
        $iterValue = $this->toIterator($value);
        $length = max(ceil(($stop - $start) / $step), 0);
        for ($i = 0; $i < $length; $start += $step) {
            // advance the iterator
            if ($iterValue->valid()) {
                $v = $iterValue->current();
                $iterValue->next();
                $this->insertWhere($start, $v);
            } else {
                $this->deleteWhere($start);
            }
            $i++;
        }
        // the value still have more item, but the range indices already exit
        // so insert the remaining item where the indices stop
        if ($iterValue->valid()) {
            $where = max(0, $stop);
            while ($iterValue->valid()) {
                $v = $iterValue->current();
                $iterValue->next();
                $this->insertWhere($where, $v);
            }
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param  mixed $index The index of item to unse
     * @return void
     */
    public function offsetUnset($index)
    {
        if (! $index instanceof Slice) {
            $index = $this->guardedSeek($index, __METHOD__);
            $this->deleteWhere($index);

            return;
        }
        list($start, $stop, $step) = $index->indices(count($this));
        $length = max(ceil(($stop - $start) / $step), 0);
        for ($i = 0; $i < $length; $start += $step) {
            try {
                unset($this[$start]);
            } catch (OutOfRangeException $e) {
                break;
            }
            $i++;
        }
    }

    /**
     * Determine of the given index is exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $index
     */
    public function offsetExists($index)
    {
        return $this->calculateOffset($index) < (count($this) - 1);
    }

    /**
     * Get an item at a given offset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param  mixed $index
     * @return mixed
     */
    public function offsetGet($index)
    {
        if (! $index instanceof Slice) {
            $n = $this->guardedSeek($index, __METHOD__);

            return $this->items[$n];
        }

        list($start, $stop, $step) = $index->indices(count($this));
        $length = max(ceil(($stop - $start) / $step), 0);
        $container = new static();
        for ($i = 0; $i < $length; $start += $step) {
            try {
                $container->push($this[$start]);
            } catch (OutOfRangeException $e) {
                break;
            }
            $i++;
        }

        return $container;
    }

    /**
     * Resize the underlying storage, only resize when necessary. When resizing
     * we allocate more than newsize, to avoid resizing each item being added.
     *
     * @param  integer $newsize
     * @return void
     */
    private function storageResize($newsize)
    {
        $allocated = $this->items->getSize();
        if ($allocated >= $newsize && $newsize >= ($allocated >> 1)) {
            $this->size++;

            return;
        }
        $newAllocated = ($newsize >> 3) + ($newsize < 9 ? 3 : 6);

        if ($newAllocated > (PHP_INT_MAX - $newsize)) {
            throw new \RuntimeException(sprintf(
                    'Trying to allocated too big array'
                ));
        } else {
            $newAllocated += $newsize;
        }

        if ($newsize == 0) {
            $newAllocated = 0;
        }
        $this->items->setSize($newAllocated);

        $this->size++;
    }

    /**
     * Internal routing to insert item to underlying storage
     *
     * @param  integer $where
     * @param  mixed   $valud
     * @return void
     */
    private function insertWhere($where, $value)
    {
        $n = $this->size;
        // make sure the storage is not full
        $this->storageResize($n + 1);
        if ($where < 0) {
            $where += $n;
            if ($where < 0) {
                $where = 0;
            }
        }
        if ($where > $n) {
            $where = $n;
        }

        for ($i = $n; --$i >= $where;) {
            $this->items[$i+1] = $this->items[$i];
        }
        $this->items[$where] = $value;
    }

    /**
     * Delete an item for the given index `where`, work best if the item being deleted
     * is last element.
     *
     * @param  integer $where
     * @return void
     */
    private function deleteWhere($where)
    {
        $n = count($this);
        if ($where < 0) {
            $where += $n;
            if ($where < 0) {
                $where = 0;
            }
        }
        if ($where >= $n) {
            $where = $n - 1;
        }
        for ($i = $where; $i < $n; $i++) {
            $this->items[$i] = $this->items[$i+1];
        }
        $this->size--;
    }

    /**
     * Just utils to convert any iterable to iterator object, used when slicing
     *
     * @param \Traversable|array $iterable
     */
    private function toIterator($iterable)
    {
        if ($iterable instanceof Iterator) {
            return $iterable;
        }
        if ($iterable instanceof IteratorAggregate) {
            return $iterable->getIterator();
        }
        if (is_array($iterable)) {
            return new \ArrayIterator($iterable);
        }
        throw new \InvalidArgumentException('Argument must be iterable');
    }

    /**
     * Internal routine to callculate the actual index, because we support negative
     * indexing. If the calculateOffset return invalid offset then it throws
     * OutOfRangeException
     *
     * @param  integer $index  The index to callculate
     * @param  string  $method method being called by user, used to give better error message
     * @return integer The callculated index, this index can be used to access SplFixedArray
     */
    private function guardedSeek($index, $method)
    {
        $index = $this->calculateOffset($index);
        // calculateOffset may return negative value
        if ($index < 0 || $index > (count($this) - 1)) {
            throw new OutOfRangeException(
                "{$method} was called with invalid index: {$index}"
            );
        }

        return $index;
    }
}
