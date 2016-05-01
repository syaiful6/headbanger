<?php
namespace Headbanger\Utils;

use SplFixedArray;
/**
 * To allow lazy initialization of HashEntry, previosly we iterate the SplFixedArray
 * and set all slots to instance of HashEntry
 */
class HashStorage extends SplFixedArray
{
    /**
     *
     */
    public function offsetGet($index)
    {
        $entry = parent::offsetGet($index);
        if (! $entry instanceof HashEntry) {
            $entry = $this[$index] = new HashEntry(); // initialize here
        }
        return $entry;
    }
}
