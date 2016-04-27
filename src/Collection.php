<?php
namespace Headbanger;

use Countable;

interface Collection extends \Traversable, Countable
{
    /**
     * Return true if this collection is empty, false otherwise
     *
     * @return boolean
     */
    public function isEmpty();

    /**
     * Return true if this collections contains x
     */
    public function contains($x);
}
