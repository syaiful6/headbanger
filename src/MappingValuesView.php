<?php
namespace Headbanger;

class MappingValuesView extends MappingView
{
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
