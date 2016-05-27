<?php

namespace Headbanger;

/**
 * An interface for class that support to be used as key in real Mapping.
 *
 * @author Syaiful Bahri <syaiful@pengunjungblog.com>
 */
interface Hashable
{
    /**
     * Return hash code for mapping. No need to unique.
     *
     * @return string
     */
    public function hashCode();

    /**
     * Test other object or acceptable type passed is equal.
     *
     * @param  mixed   $other
     * @return bool
     */
    public function equals($other);
}
