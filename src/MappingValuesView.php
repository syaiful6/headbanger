<?php

namespace Headbanger;

use Countable;
use IteratorAggregate;

class MappingValuesView implements IteratorAggregate, Countable
{
    use MappingView;
    /**
     *
     */
    public function contains($value)
    {
        foreach ($this->mapping as $key) {
            if ($value === $this->mapping[$key]) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     */
    public function getIterator()
    {
        foreach ($this->mapping as $key) {
            yield $this->mapping[$key];
        }
    }
}
