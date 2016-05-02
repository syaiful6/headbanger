<?php

namespace Headbanger;

class Set extends BaseSet
{
    /**
     * @param HashTable
     */
    protected $members;

    /**
     *
     */
    public function __construct($initial = null)
    {
        $this->members = new HashMap();
        if ($initial !== null) {
            $this->_update($initial);
        }
    }

    /**
     *
     */
    public function count()
    {
        return count($this->members);
    }

    /**
     *
     */
    public function contains($elem)
    {
        return $this->members->contains($elem);
    }

    /**
     *
     */
    public function clear()
    {
        $this->members->clear();
    }

    /**
     *
     */
    public function add($elem)
    {
        $this->members[$elem] = true;
    }

    /**
     *
     */
    public function remove($elem)
    {
        unset($this->members[$elem]);
    }

    /**
     *
     */
    public function discard($elem)
    {
        try {
            $this->remove($elem);
        } catch (OutOfBoundsException $e) {
            // pass
        } catch (UnderflowException $e) {
            //pass
        }
    }

    /**
     *
     */
    public function pop()
    {
        return $this->members->pop()[0];
    }

    /**
     *
     */
    public function _update($other)
    {
        foreach ($other as $el) {
            $this->members[$el] = true;
        }
    }

    /**
     *
     */
    public function getIterator()
    {
        foreach ($this->members as $m) {
            yield $m;
        }
    }
}
