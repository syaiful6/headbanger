<?php
namespace Headbanger;

use SplFixedArray;
use OutOfRangeException;
use Headbanger\Utils\Slice;

class ArrayList extends MutableSequence
{
    protected $size = 0;

    protected $items;

    /**
     *
     */
    public function __construct($iterable = null)
    {
        $this->items = new SplFixedArray(0); // just initialize with 0 capacity
        if ($iterable !== null) {
            if (is_array($iterable)) {
                $this->extend(\array_values($iterable));
            } else {
                $this->extend($iterable);
            }
        }
    }

    /**
     *
     */
    public function count()
    {
        return $this->size;
    }

    /**
     *
     */
    public function insert($index, $value)
    {
        $slice = new Slice($index, $index);
        $this[$slice] = [$value];
    }

    /**
     *
     */
    public function offsetSet($index, $value)
    {
        if (! $index instanceof Slice) {
            $index = $this->guardedSeek($key, __METHOD__);
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
     * [offsetUnset description]
     * @param  [type] $key [description]
     * @return [type] [description]
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
     *
     */
    public function offsetExists($index)
    {
        return $this->calculateOffset($index) < (count($this) - 1);
    }

    /**
     *
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
                $container->append($this[$start]);
            } catch (OutOfRangeException $e) {
                break;
            }
            $i++;
        }

        return $container;
    }

    /**
     *
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
     *
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
     *
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
     *
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
     * [guardedSeek description]
     * @param  [type] $index  [description]
     * @param  [type] $method [description]
     * @return [type] [description]
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
