<?php
namespace Headbanger;

use SplFixedArray;
use UnderflowException;
use OutOfBoundsException;

class Hashtable extends MutableMapping
{
    private $table;

    private $filled = 0;

    private $used = 0;

    private $mask;

    private $lookup;

    private $hashedString = [];

    const MINSIZE = 8;
    const PERTURB_SHIFT = 5;

    /**
     * @param \Traversable|array $initial
     */
    function __construct($initial = null)
    {
        $this->clear();
        if ($initial !== null) {
            $this->_update($initial);
        }
    }

    /**
     *
     */
    public static function fromKeys($keys, $value=0)
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
    function clear()
    {
        $this->filled = $this->used = 0;
        $this->mask = self::MINSIZE - 1;
        $this->table = $this->createNewTable(self::MINSIZE);
        $this->lookup = 'defaultLookup';
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
    public function popItem()
    {
        if ($this->isEmpty()) {
            throw new UnderflowException(sprintf(
                'Trying to pop item of an empty %s', get_class($this)));
        }
        $entry0 = $this->table[0];
        $entry = $entry0;
        $i = 0;
        if ($entry0->value === null) {
            $i = $entry0->hash;
            if ($i > $this->mask || $i < $i) {
                $i = 1;
            }
            $entry = $this->table[$i];
            while($entry->value === null) {
                $i += 1;
                if ($i > $this->mask) {
                    $i = 1;
                }
                $entry = $this->table[$i];
            }
        }
        $res = [$entry->key, $entry->value];
        $this->_del($entry);
        # Set the next place to start.
        $entry0->hash = $i + 1;
        return $res;
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
        throw new OutOfBoundsException("no such key in collection");
    }

    /**
     *
     */
    public function offsetSet($key, $value)
    {
        if ($key === null || $value === null) {

            throw new \InvalidArgumentExceptin("Invalid key. Key cant be Null");
        }
        $oldUsed = $this->used;
        $this->_insert($key, $value);
        if (!($this->used > $oldUsed
            && $this->filled * 3 >= ($this->mask + 1) * 2 )) {
            return;
        }
        $factor = $this->used > 5000 ? 2 : 4;
        $this->_resize($factor * $this->used);
    }

    /**
     *
     */
    public function offsetUnset($key)
    {
        if ($key === null) {

            throw new \InvalidArgumentExceptin("Invalid key. Key cant be Null");
        }
        if ($this->isEmpty()) {
            throw new UnderflowException(
                "Trying to delete an item in empty collection");
        }
        $hash = $this->computeHash($key);
        $lookup = $this->lookup;
        $entry = $this->$lookup($hash, $key);
        if ($entry->value === null) {

            throw new OutOfBoundsException("no such key in collection");
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
        while (true) {
            if ($used !== count($this)) {
                throw new \RuntimeException(
                    "Hashtable size changed during iteration");
            }
            $i = $pos;
            if ($i > $size) {
                break; // exit
            }
            $entry = $this->table[$i];
            while ($i <= $size && $entry->value === null) {
                $i += 1;
                $entry = $this->table[$i];
            }
            $pos = $i + 1; // advance this for next
            yield $entry->key;
        }
    }

    /**
     * the fast version if all the key is just string or integer
     */
    private function defaultLookup($hash, $key)
    {
        if (!is_string($key) && !is_integer($key)) {
            $this->lookup = 'lookupEntry';

            return $this->lookupEntry($hash, $key);
        }
        $mask = $this->mask;
        $i = $hash & $mask;
        $entry = $this->table[$i];
        // not used yet
        if ($entry->key === null) {

            return $entry;
        }
        $free = null;
        if ($entry->key === $this->dummy) {
            $free = $entry;
        } else if ($entry->hash === $hash && $entry->key === $key) {

            return $entry;
        }
        // collision resolution
        for ($pertub = $hash; ;$pertub >>= self::PERTURB_SHIFT) {
            $i = ($i << 2) + $i + $pertub + 1;
            $entry = $this->table[$i & $this->mask];
            if ($entry->key === null) {

                return $free === null ? $entry : $free;
            }
            if ($entry->hash === $hash && $entry->key === $key) {

                return $entry;
            } else if ($entry->key === $this->dummy && $free === null) {

                $free = $dummy;
            }
        }

        throw new \RuntimeException('Failed to find slot on hashtable');
    }

    /**
     * the key is appear to
     */
    private function lookupEntry($hash, $key)
    {
        $mask = $this->mask;
        $i = $hash & $mask;
        $entry = $this->table[$i];
        // not used yet
        if ($entry->key === null) {

            return $entry;
        }
        $free = null;
        if ($entry->key === $this->dummy) {
            $free = $entry;
        } else if ($entry->hash === $hash && $this->keyAreEqual($entry->key, $key)) {

            return $entry;
        }
        // collision resolution
        for ($pertub = $hash; ;$pertub >>= self::PERTURB_SHIFT) {
            $i = ($i << 2) + $i + $pertub + 1;
            $entry = $this->table[$i & $this->mask];
            if ($entry->key === null) {

                return $free === null ? $entry : $free;
            }
            if ($entry->hash === $hash && $this->keyAreEqual($entry->key, $key)) {

                return $entry;
            } else if ($entry->key === $this->dummy && $free === null) {
                $free = $dummy;
            }
        }
        // blow the application if we here
        throw new \RuntimeException('Failed to find slot on hashtable');
    }

    /**
     *
     */
    private function keyAreEqual($a, $b)
    {
        if (is_object($a) && $a instanceof Hashable) {
            return $a->isEqual($b);
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
            throw new \InvalidArgumentExceptin(sprintf(
                    'The size storage must be greater than %d',
                    self::MINSIZE
                ));
        }
        // test if the size is power two
        if(($size & ($size - 1)) !== 0) {
            throw new \InvalidArgumentExceptin(
                    'You should provide size with number power 2'
                );
        }
        $table = new SplFixedArray($size);
        $this->dummy = spl_object_hash($table);
        foreach ($table as $k => $b) {
            $table[$k] = new HashEntry();
        }
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
        $oldTable = clone $this->table;
        // create new table will create new dummy
        $oldUsed = $this->used;
        $oldSize = $oldTable->getSize();
        $oldDummy = $this->dummy;
        $this->table = $this->createNewTable($newsize);
        $this->used = $this->filled = 0;

        for ($i = $j = 0; $i < $oldUsed && $j < $oldSize; $j++) {
            $entry = $oldTable[$j];
            if ($entry->value !== Null) {
               $this->insertClean($entry->hash, $entry->key, $entry->value);
               $i++;
            } elseif ($entry->key === $oldDummy) {
                $entry->key = null;
            }
        }
        $this->mask = $newsize - 1;
    }

    /**
     *
     */
    private function _insert($key, $value)
    {
        $hash = $this->computeHash($key);
        $lookup = $this->lookup;
        $entry = $this->$lookup($hash, $key);
        if ($entry->value === null) {
            $this->used += 1;
            if ($entry->key !== $this->dummy) {
                $this->filled += 1;
            }
        }
        $entry->key = $key;
        $entry->hash = $hash;
        $entry->value = $value;
    }

    /**
     *
     */
    private function _del($entry)
    {
        $entry->key = $this->dummy;
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
        $pertub = $hash;
        while ($newEntry->key !== null) {
            $i = ($i << 2) + $i + $pertub + 1;
            $newEntry = $this->table[$i & $this->mask];
        }
        $newEntry->hash = $hash;
        $newEntry->key = $key;
        $newEntry->value = $value;
        $this->used += 1;
        $this->filled += 1;
    }

    /**
     *
     */
    private function computeHash($key)
    {
        if (is_object($key) && $object instanceof Hashable) {
            $hash = $key->hashCode();
        } elseif (is_numeric($key)) {
            $hash = $key;
        } elseif (is_string($key)) {
            $hash = $this->hashString($key);
        } else {
            throw new \InvalidArgumentExceptin(sprintf(
                '%s type can\'t be hashed',
                gettype($key)
            ));
        }
        return $hash;
    }

    /**
     * [hashString description]
     * @param  [type] $string [description]
     * @return [type]         [description]
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
}
