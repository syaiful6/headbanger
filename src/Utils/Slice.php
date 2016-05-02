<?php

namespace Headbanger\Utils;

/**
 * Represent slicing indices for use with sequence
 */
class Slice
{
    private $start;

    private $stop;

    private $step;

    /**
     *
     */
    public function __construct(...$args)
    {
        $count = count($args);
        if ($count === 1) {
            $this->start = $args[0];
        } else {
            if ($count < 3) {
                $add = array_fill(0, 3 - $count, null);
                $args = array_merge($args, $add);
            }
            list($start, $stop, $step) = $args;
            $this->start = $start;
            $this->stop = $stop;
            $this->step = $step;
        }
    }

    /**
     *
     */
    public function indices($length)
    {
        $length = intval($length);
        if ($this->step === null) {
            $stepIsNegative = false;
            $step = 1;
        } else {
            $step = intval($this->step);
            if ($step === 0) {
                throw new \InvalidArgumentException(sprintf(
                        'Step can\'t be zero'
                    ));
            }
            $stepIsNegative = $step < 0;
        }
        if ($stepIsNegative) {
            $lower = -1;
            $upper = $length - 1;
        } else {
            $lower = 0;
            $upper = $length;
        }
        if ($this->start === null) {
            $start = $stepIsNegative ? $upper : $lower;
        } else {
            $start = intval($this->start);
            if ($start < 0) {
                $start = $start + $length;
                if ($start < $lower) {
                    $start = $lower;
                }
            } elseif ($start > $upper) {
                $start = $upper;
            }
        }
        if ($this->stop === null) {
            $stop = $stepIsNegative ? $lower : $upper;
        } else {
            $stop = intval($this->stop);
            if ($stop < 0) {
                $stop = $stop + $length;
                if ($stop < $lower) {
                    $stop = $lower;
                }
            } elseif ($stop > $upper) {
                $stop = $upper;
            }
        }

        return [$start, $stop, $step];
    }
}
