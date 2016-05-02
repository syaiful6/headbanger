<?php

namespace Headbanger;

trait MappingView
{
    /**
     * @var Mapping
     */
    protected $mapping;

    /**
     * @var mapping
     */
    public function __construct(Mapping $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     *
     */
    public function count()
    {
        return count($this->mapping);
    }

    /**
     *
     */
    public function getIterator()
    {
        return $this->mapping->getIterator();
    }
}
