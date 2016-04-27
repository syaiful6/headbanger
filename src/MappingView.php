<?php
namespace Headbanger;

use Countable;
use IteratorAggregate;

abstract class MappingView extends implements Countable, IteratorAggregate
{
	/**
	 * @var Mapping
	 */
	protected $mapping;


    protected $used;

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
