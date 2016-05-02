<?php

namespace Headbanger;

use RuntimeException;
use OutOfRangeException;
use Headbanger\Utils\Slice;
use Headbanger\Exceptions\ValueException;

class Str extends Sequence implements Hashable
{
    protected $string;

    protected $encoding;

    private $_hash;

    const ASCII_LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';

    const ASCII_UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const ASCII_LETTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const DIGITS = 0123456789;

    const HEXDIGITS = '0123456789abcdefABCDEF';

    const OCTDIGITS = 01234567;

    const WHITESPACE = " \t\n\r\v\f";

    const PUNCTUATION = '!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~';

    const PRINTABLE = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!\"#$%&'()*+,-./:;<=>?@[\]^_`{|}~\t\n\r\v\f";

    /**
     *
     */
    public function __construct($string, $encoding = null)
    {
        if ($encoding === null) {
            $encoding = mb_internal_encoding();
        }
        // Cast string variable passed to string
        $this->string = (string) $string;
        $this->encoding = $encoding;
    }

    /**
     * Magic method when cast this object as string
     * like (string) $this
     *
     * @return string
     */
    public function __toString()
    {
        return $this->string;
    }

    /**
     *
     */
    public function count()
    {
        return mb_strlen($this->string);
    }

    /**
     *
     */
    public function contains($other)
    {
        if ($other instanceof Str ||
            (is_object($other) && method_exists($other, '__toString'))) {
            $other = strval($other);
        }

        return mb_strpos($this->string, $other) !== false;
    }

    /**
     * Find the position for the given substring in string
     * using start and stop. Not like PHP do, this method will always results
     * integer. When it can't see the substring, it will return -1
     *
     */
    public function find($sub, $start = 0, $stop = PHP_INT_MAX)
    {
        $slice = new Slice($start, $stop);
        list($start, $stop, $step) = $slice->indices(count($this));
        if ($sub instanceof Str ||
            (is_object($sub) && method_exists($sub, '__toString'))) {
            $sub = strval($sub);
        }
        $pos = mb_strpos($this->string, $sub, $start, $this->encoding);
        if ($pos === false || $pos > $stop) {
            return -1;
        }

        return $pos;
    }

    /**
     * Like static#find but will raise exception if not found
     *
     */
    public function index($sub, $start = 0, $stop = PHP_INT_MAX)
    {
        $pos = $this->find($sub, $start, $end);

        if ($pos < 0) {
            throw new ValueException(sprintf(
                'Substring %s not found in %s', $sub, $this->string
            ));
        }

        return $pos;
    }

    /**
     * Find the position for the given substring in string
     * using start and stop. Not like PHP do, this method will always results
     * integer. When it can't see the substring, it will return -1
     *
     */
    public function rfind($sub, $start = 0, $stop = PHP_INT_MAX)
    {
        $key = new Slice($start, $stop);
        list($start, $stop, $step) = $this->indices(count($this));
        if ($sub instanceof Str ||
            (is_object($sub) && method_exists($sub, '__toString'))) {
            $sub = strval($sub);
        }
        $pos = mb_strrpos($this->string, $sub, $start, $this->encoding);
        if ($pos === false || $pos > $stop) {
            return -1;
        }

        return $pos;
    }

    /**
     *
     */
    public function rindex($sub, $start = 0, $stop = PHP_INT_MAX)
    {
        $pos = $this->rfind($sub, $start, $stop);
        if ($pos < 0) {
            throw new ValueException(sprintf(
                'Substring %s not found in %s', $sub, $this->string
            ));
        }

        return $pos;
    }

    /**
     *
     */
    public function countSub($sub, $start = 0, $stop = PHP_INT_MAX)
    {
        $key = new Slice($start, $stop);

        return \mb_substr_count(strval($this[$key]), $sub, $this->encoding);
    }

    /**
     * Replace tab character with spaces. The length of space is according
     * to the parameter, default to 8.
     *
     * @param integer $tabsize The tabsize to apply
     */
    public function expandTabs($tabsize = 8)
    {
        if ($tabsize < 0) {
            throw new ValueException(sprintf(
                'Argument 1 passed to %s@%s must be positive.',
                get_called_class(),
                __FUNCTION__
            ));
        }

        $out = \str_replace("\t", \str_repeat(' ', $tabsize), $this->string);

        return new static($out);
    }

    /**
     * Partition the undelyting string, and return 3 tuple
     * when can't find the sep, the fist index tuple is original string
     *
     */
    public function partition($sep)
    {
        if ($this->find($sep) < 0) {
            return [$this->string, '', ''];
        }

        $exploded = \explode($sep, $this->string, 2);

        return [$exploded[0], $sep, $exploded[1]];
    }

    /**
     *
     */
    public function rpartition($sep)
    {
        if ($this->rfind($sep) < 0) {
            return ['', '', $str];
        }

        $result = \array_map('\\strrev',
            \explode($sep, \strrev($this->string), 2)
        );

        return [$result[1], $sep, $result[0]];
    }

    /**
     *
     */
    public function replace($old, $new, $count = -1)
    {
        if ($count === null || $count < 0) {
            return new static(\str_replace($old, $new, $this->string));
        }

        $out = \preg_replace_callback('/['.$old.']/', function ($str) use ($new) {
            return $new;
        }, $this->string, $count);

        return new static($out);
    }

    /**
     *
     */
    public function splitLines($keepend = false)
    {
        if (!$keepend) {
            return preg_split('#$\R?^#m', $this->string);
        }

        return preg_split("#(?<=\n)(?<=\n|.)#m", $this->string);
    }

    /**
     * Return an array of the words in the string,
     * using sep as the delimiter string. If maxsplit is given,
     * at most maxsplit splits are done
     * (thus, the list will have at most maxsplit+1 elements)
     *
     * @param string  $str       The string to split
     * @param string  $delimeter delimeter or pattern to split the string
     * @param integer $maxsplit
     */
    public function rsplit($delimeter = null, $maxsplit = -1)
    {
        if ($maxsplit !== -1) {
            $maxsplit += 1;
        }
        if ($delimeter == null) {
            $delimeter = '[\s]+';
        } else {
            $delimeter = preg_quote($delimeter);
        }
        $out = preg_split('#'.$delimeter.'#', strrev($this->string),
                           $maxsplit);

        return array_reverse(array_map('\\strrev', $out));
    }

    /**
     * Return an array of the words in the string,
     * using sep as the delimiter string. If maxsplit is given,
     * at most maxsplit splits are done
     * (thus, the list will have at most maxsplit+1 elements)
     *
     * @param string  $str       The string to split
     * @param string  $delimeter delimeter or pattern to split the string
     * @param integer $maxsplit
     */
    public function split($delimeter = null, $maxsplit = -1)
    {
        if ($maxsplit !== -1) {
            $maxsplit += 1;
        }
        if ($delimeter == null) {
            $delimeter = '[\s]+';
        } else {
            $delimeter = preg_quote($delimeter);
        }

        return preg_split('#'.$delimeter.'#', $this->string, $maxsplit);
    }

    /**
     *
     */
    public function trim($charmast = " \t\n\r\0\x0B")
    {
        return new static(trim($this->string, $charmast));
    }

    /**
     *
     */
    public function rtrim($charmast = " \t\n\r\0\x0B")
    {
        return new static(rtrim($this->string, $charmast));
    }

    /**
     *
     */
    public function ltrim($charmast = " \t\n\r\0\x0B")
    {
        return new static(ltrim($this->string, $charmast));
    }


    /**
     * Capitalize the underlying string
     */
    public function capitalize()
    {
        $capitalize = mb_convert_case($this->string, MB_CASE_TITLE, $this->encoding);

        return new static($capitalize);
    }

    /**
     *
     */
    public function lower()
    {
        $lower = mb_convert_case($this->string, MB_CASE_LOWER, $this->encoding);

        return new static($lower);
    }

    /**
     *
     */
    public function startsWith($needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && strpos($this->string, $needle) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string|array $needles
     * @return bool
     */
    public function endsWith($needles)
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle === substr($this->string, -strlen($needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     */
    public static function fromIterable($iterable)
    {
        $str = '';
        foreach ($iterable as $value) {
            $str .= $value;
        }

        return new static($str);
    }

    /**
     *
     */
    public function join($iterable)
    {
        $str = '';
        $first = true;
        foreach ($iterable as $value) {
            if ($first) {
                $str .= $value;
                $first = false;
            } else {
                $str .= $this->string.$value;
            }
        }

        return new static($out);
    }

    /**
     *
     */
    public function isAlnum()
    {
        return ctype_alnum($this->string);
    }

    /**
     *
     */
    public function isSpace()
    {
        return ctype_space($this->string);
    }

    /**
     *
     */
    public function isAlpha()
    {
        return ctype_alpha($this->string);
    }

    /**
     *
     */
    public function isDigit()
    {
        return ctype_digit($this->string);
    }

    /**
     *
     */
    public function isNumeric()
    {
        return is_numeric($this->string);
    }

    /**
     *
     */
    public function isLower()
    {
        return ctype_lower($this->string);
    }

    /**
     *
     */
    public function isUpper()
    {
        return ctype_upper($this->string);
    }

    /**
     *
     */
    public function isPrintable()
    {
        return ctype_print($this->string);
    }

    /**
     *
     */
    public function offsetSet($key, $value)
    {
        throw new RuntimeException(sprintf(
                '%s does\'t support item assigment.',
                get_called_class()
            ));
    }

    /**
     *
     */
    public function offsetUnset($key)
    {
        throw new RuntimeException(sprintf(
                '%s does\'t support item deletion.',
                get_called_class()
            ));
    }

    /**
     *
     */
    public function offsetGet($index)
    {
        if (! $index instanceof Slice) {
            $index = $this->calculateOffset($index);
            foreach ($this as $i => $elem) {
                if ($i == $index) {
                    return $elem;
                }
            }
            throw new OutOfRangeException(sprintf(
                    'Look up %d not found in range',
                    $index
                ));
        }

        list($start, $stop, $step) = $index->indices(count($this));
        $length = max(ceil(($stop - $start) / $step), 0);
        $container = [];
        for ($i = 0; $i < $length; $start += $step) {
            try {
                $container[] = $this[$start];
            } catch (OutOfRangeException $e) {
                break;
            }
            $i++;
        }

        return static::fromIterable($container);
    }

    /**
     * Determine of the given index is exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $index
     */
    public function offsetExists($index)
    {
        return $this->calculateOffset($index) < (count($this) - 1);
    }

    /**
     * [hashString description]
     * @param  [type] $string [description]
     * @return [type] [description]
     */
    public function hashCode()
    {
        if ($this->_hash) {
            return $this->_hash;
        }
        $hash = 0;
        $str = $this->string;
        for ($i = 0, $length = $this->len(); $i < $length; $i++) {
            $hash ^= (31 * $hash) + ord($str[$i]);
        }
        $this->_hash = $hash;
        return $hash;
    }

    /**
     *
     */
    public function equals($other)
    {
        if ($other instanceof Str) {
            return $this->string === strval($other);
        } elseif (is_string($other)) {
            return $this->string === $other;
        }

        throw new ValueException(
                'You can only compare string object with string object or string type'
            );
    }

    /**
     *
     */
    public function getIterator()
    {
        for ($i = 0, $size = count($this); $i < $size; $i++) {
            yield mb_substr($this->string, $i, 1, $this->encoding);
        }
    }
}
