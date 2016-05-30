<?php

namespace Headbanger;

use Headbanger\Utils\HashStorage;
use UnderflowException;
use OutOfBoundsException;

class HashMap extends MutableMapping
{
    private $table;

    private $used = 0;

    protected $usable;

    private $mask;

    private $lookup;

    private $hashedString = [];

    private $dummy;

    const MINSIZE = 8;
    const PERTURB_SHIFT = 5;

    /**
     * @param \Traversable|array $initial
     */
    public function __construct($initial = null)
    {
        $this->clear();
        if ($initial !== null) {
            $this->_update($initial);
        }
    }

    /**
     *
     */
    public static function fromKeys($keys, $value = 0)
    {
        $dict = new self();
        foreach ($keys as $key) {
            $dict[$key] = $value;
        }

        return $dict;
    }

    /**
     *
     */
    public function count()
    {
        return $this->used;
    }

    /**
     *
     */
    public function clear()
    {
        $this->used = 0;
        $this->table = $this->createNewTable(self::MINSIZE);
        $this->lookup = 'stringOnlyLookupNoDummy';
    }

    /**
     *
     */
    public function update($iterable)
    {
        $this->_update($iterable);
    }

    /**
     *
     */
    public function offsetGet($key)
    {
        if ($this->used === 0) {
            return $this->offsetMissing($key);
        }
        $hash = $this->computeHash($key);
        $lookup = $this->lookup;
        $entry = $this->$lookup($hash, $key);
        if ($entry->value === null) {
            return $this->offsetMissing($key);
        }

        return $entry->value;
    }

    /**
     *
     */
    protected function offsetMissing($key)
    {
        throw new OutOfBoundsException("no such $key key in collection");
    }

    /**
     *
     */
    public function offsetSet($key, $value)
    {
        if ($key === null || $value === null) {
            throw new \InvalidArgumentException('Invalid key or value. Key and value cant be Null');
        }
        $this->_insert($key, $value);
    }

    /**
     *
     */
    public function offsetUnset($key)
    {
        if ($key === null) {
            throw new \InvalidArgumentExceptin('Invalid key. Key cant be Null');
        }
        if ($this->isEmpty()) {
            throw new UnderflowException(
                'Trying to delete an item in empty collection');
        }
        $hash = $this->computeHash($key);
        $lookup = $this->lookup;
        $entry = $this->$lookup($hash, $key);
        if ($entry->value === null) {
            throw new OutOfBoundsException("no such $key key in collection");
        }
        $this->_del($entry);
    }

    /**
     *
     */
    public function setDefault($key, $value = 0)
    {
        if ($this->isEmpty()) {
            $this[$key] = $value;

            return $value;
        }
        $hash = $this->computeHash($key);
        $lookup = $this->lookup;
        $entry = $this->$lookup($hash, $key);

        if ($entry->value === null) {
            $this[$key] = $value;
        }

        return $value;
    }

    /**
     *
     */
    public function getIterator()
    {
        $used = $this->used;
        $pos = 0;
        $size = $this->mask;
        for ($i = $pos; $i <= $size; $i++) {
            if ($used !== count($this)) {
                throw new \RuntimeException(
                    'Hashtable size changed during iteration');
            }
            $entry = $this->table[$i];
            if ($entry->key === null || $entry->key === $this->dummy) {
                continue;
            }
            yield $entry->key;
        }
    }

    /**
     *
     */
    private function stringOnlyLookup($hash, $key)
    {
        if (! is_string($key)) {
            $this->lookup = 'lookupEntry';

            return $this->lookupEntry($hash, $key);
        }
        $mask = $this->mask;
        $i = $hash & $mask;
        $entry = $this->table[$i];
        // not used yet
        if ($entry->key === null || strcmp($entry->key, $key) === 0) {
            return $entry;
        }
        $free = null;
        if ($entry->key === $this->dummy) {
            $free = $entry;
        } elseif ($entry->hash === $hash && strcmp($entry->key, $key) === 0) {
            return $entry;
        }
        // collision resolution
        for ($perturb = $hash; ;$perturb >>= self::PERTURB_SHIFT) {
            $i = ($i << 2) + $i + $perturb + 1;
            $entry = $this->table[$i & $this->mask];
            if ($entry->key === null) {
                return $free === null ? $entry : $free;
            }
            if ($entry->hash === $hash && strcmp($entry->key, $key) === 0) {
                return $entry;
            } elseif ($entry->key === $this->dummy && $free === null) {
                $free = $entry;
            }
        }

        throw new \RuntimeException('Failed to find slot on hashtable');
    }

    /**
     *
     */
    private function stringOnlyLookupNoDummy($hash, $key)
    {
        if (! is_string($key)) {
            $this->lookup = 'lookupEntry';

            return $this->lookupEntry($hash, $key);
        }
        $mask = $this->mask;
        $i = $hash & $mask;
        $entry = $this->table[$i];

        assert($entry->key === null || is_string($entry->key));
        if ($entry->key === null || strcmp($entry->key, $key) === 0 ||
            ($entry->hash === $hash && strcmp($entry->key, $key) === 0)) {
            return $entry;
        }

        for ($perturb = $hash; ;$perturb >>= self::PERTURB_SHIFT) {
            $i = ($i << 2) + $i + $perturb + 1;
            $entry = $this->table[$i & $this->mask];
            if ($entry->key === null ||
                ($entry->hash === $hash && strcmp($entry->key, $key) === 0)) {
                return $entry;
            }
        }
    }

    /**
     * the key is appear to.
     */
    private function lookupEntry($hash, $key)
    {
        $mask = $this->mask;
        $i = $hash & $mask;
        $entry = $this->table[$i];
        // not used yet, or this key is identical just return it
        if ($entry->key === null || $entry->key === $key) {
            return $entry;
        }
        $free = null;
        if ($entry->key === $this->dummy) {
            $free = $entry;
        } elseif ($entry->hash === $hash && $this->keyAreEqual($entry->key, $key)) {
            // the hash of this key are equals, test if the key indeed equals
            return $entry;
        }
        // collision resolution
        for ($perturb = $hash; ;$perturb >>= self::PERTURB_SHIFT) {
            $i = ($i << 2) + $i + $perturb + 1;
            $entry = $this->table[$i & $this->mask];
            if ($entry->key === null) {
                return $free === null ? $entry : $free;
            }
            if ($entry->hash === $hash && $this->keyAreEqual($entry->key, $key)) {
                return $entry;
            } elseif ($entry->key === $this->dummy && $free === null) {
                $free = $entry;
            }
        }
    }

    /**
     *
     */
    private function findEmptySlot($hash, $key)
    {
        if (! is_string($key)) {
            $this->lookup = 'lookupEntry';
        }
        $i = $hash & $this->mask;
        $entry = $this->table[$i];
        for ($perturb = $hash; $entry->key !== null; $perturb >>= self::PERTURB_SHIFT) {
            $i = ($i << 2) + $i + $perturb + 1;
            $entry = $this->table[$i & $this->mask];
        }
        assert($entry->value === null);

        return $entry;
    }

    /**
     *
     */
    private function keyAreEqual($a, $b)
    {
        if (is_object($a) && $a instanceof Hashable) {
            return $a->equals($b);
        }
        // otherwise just compare directly
        return $a === $b;
    }

    /**
     *
     */
    private function createNewTable($size)
    {
        if ($size < self::MINSIZE) {
            throw new \InvalidArgumentException(sprintf(
                    'The size storage must be greater than %d',
                    self::MINSIZE
                ));
        }
        // test if the size is power two
        if (($size & ($size - 1)) !== 0) {
            throw new \InvalidArgumentException(
                    'You should provide size with number power 2'
                );
        }
        $table = new HashStorage($size);
        $this->usable = $this->usableFraction($size);
        $this->dummy = spl_object_hash($table);
        $this->mask = $size - 1;

        return $table;
    }

    /**
     *
     */
    private function _resize($minused)
    {
        for ($newsize = self::MINSIZE;
             $newsize <= $minused && $newsize > 0;
             $newsize <<= 1);
        //
        if ($newsize <= 0) {
            throw new \RuntimeException(sprintf(
                    'Failed rezise hash table'
                ));
        }
        $oldTable = $this->table;
        $oldsize = $oldTable->getSize();
        $dummy = $this->dummy;
        $this->table = $this->createNewTable($newsize);

        $i = $this->used;
        for ($j = 0; $i > 0 && $j < $oldsize; $j++) {
            $entry = $oldTable[$j];
            if ($entry->value !== null) {
                $this->insertClean($entry->hash, $entry->key, $entry->value);
                $i--;
            }
        }

        $this->usable -= $this->used;
    }

    /**
     *
     */
    private function _insert($key, $value)
    {
        $hash = $this->computeHash($key);
        $lookup = $this->lookup;
        $entry = $this->$lookup($hash, $key);
        if ($entry->value !== null) {
            $entry->value = $value;
        } else {
            if ($entry->key === null) {
                // new item is added
                if ($this->usable <= 0) {
                    $this->_resize($this->growRate());
                    $entry = $this->findEmptySlot($hash, $key);
                }
                $this->usable--;
                $entry->key = $key;
                $entry->hash = $hash;
            } else {
                if ($entry->key === $this->dummy) {
                    $entry->key = $key;
                    $entry->hash = $hash;
                } else {
                    throw new \RuntimeException('invalid hash table state');
                }
            }
            $this->used++;
            $entry->value = $value;
        }
    }

    /**
     *
     */
    private function _del($entry)
    {
        $entry->key = $this->dummy;
        if ($this->lookup === 'stringOnlyLookupNoDummy') {
            $this->lookup = 'stringOnlyLookup';
        }
        $entry->value = null;
        $this->used -= 1;
    }

    /**
     *
     */
    private function insertClean($hash, $key, $value)
    {
        $i = $hash & $this->mask;
        $newEntry = $this->table[$i];

        for ($perturb = $hash; $newEntry->key !== null;
            $perturb >>= self::PERTURB_SHIFT) {
            $i = ($i << 2) + $i + $perturb + 1;
            $newEntry = $this->table[$i & $this->mask];
        }

        assert($newEntry->value === null);
        $newEntry->hash = $hash;
        $newEntry->key = $key;
        $newEntry->value = $value;
    }

    /**
     *
     */
    private function computeHash($key)
    {
        if (is_object($key)) {
            if ($key instanceof Hashable) {
                $hash = $key->hashCode();
            } else {
                $hash = $this->hashString(spl_object_hash($key));
            }
        } elseif (is_string($key)) {
            $hash = $this->hashString($key);
        } elseif (is_numeric($key) || is_bool($key)) {
            $hash = (int) $key;
        } else {
            throw new \InvalidArgumentException(sprintf(
                '%s type can\'t be hashed',
                gettype($key)
            ));
        }

        return $hash;
    }

    /**
     * Naive and dump implementation to hash string.
     *
     * @param  string $string
     * @return int
     */
    private function hashString($string)
    {
        if (isset($this->hashedString[$string])) {
            return $this->hashedString[$string];
        }
        $hash = 0;
        for ($i = 0, $length = strlen($string); $i < $length; $i++) {
            $hash ^= (31 * $hash) + ord($string[$i]);
        }
        $this->hashedString[$string] = $hash;

        return $hash;
    }

    /**
     *
     */
    private function usableFraction($n)
    {
        return ((($n << 1) + 1) / 3);
    }

    /**
     *
     */
    private function growRate()
    {
        return ($this->used * 2) + ($this->table->getSize() >> 1);
    }
}
