<?php

namespace Headbanger;

use OutOfBoundsException;

class DefaultHashMap extends HashMap
{
	/**
	 *
	 */
	protected $factory;

	/**
	 *
	 */
	public function __construct(callable $factory = null, $initial = null)
	{
		$this->factory = $factory;
		parent::__construct($initial);
	}

    /**
     *
     */
    protected function offsetMissing($key)
    {
        if ($this->factory !== null) {
            return call_user_func($this->factory);
        }
        throw new OutOfBoundsException("no such $key key in collection");
    }
}