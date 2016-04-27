<?php
namespace Headbanger;

use OutOfBoundsException;
use UnderflowException;

abstract class MutableMapping extends Mapping
{
    /**
     *
     */
    public function offsetSet($key, $value)
    {
        throw new OutOfBoundsException(sprintf(
                'Key does\'t exists in %s',
                get_called_class()
            ));
    }

    /**
     *
     */
    public function offsetUnset($key)
    {
        throw new OutOfBoundsException(sprintf(
                'Key does\'t exists in %s',
                get_called_class()
            ));
    }

    /**
     *
     */
    public function pop($key, $default = null)
    {
        $error = null;
        try {
            $value = $this[$key];
        } catch (OutOfBoundsException $e) {
            $error = $e;
        } catch(UnderflowException $e) {
            $error = $e;
        } finally {
            if ($e !== null) {
                if ($default === null) {
                    throw $e;
                }
                return $default; // default is setting, give em back
            }
            // now exception raised, so unset this key
            unset($this[$key]);

            return $value;
        }
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
        $iterator = $this->getIterator();
        if ($iterator->valid()) {
            $key = $iterator->current();
        } else {
            throw new OutOfBoundsException(
                    'No any item in mapping.'
                );
        }
        $value = $this[$key];
        unset($this[$key]);

        return [$key, $value];
    }

    /**
     *
     */
    public function clear()
    {
        try {
            while (True) {
                $this->popitem();
            }
        } catch (OutOfBoundsException $e) {
            // pass outofbound
        } catch (UnderflowException $e) {
            // pass underflow
        }
    }

     /**
     *
     */
    protected function _update($other)
    {
        if ($other instanceof Mapping) {
            foreach ($other as $key) {
                $this[$key] = $other[$key];
            }
        } elseif (is_array($other)) {
            foreach ($other as $key => $value) {
                $this[$key] = $value;
            }
        } else {
            foreach ($other as list($key, $value)) {
                $this[$key] = $value;
            }
        }
    }

    /**
     *
     */
    public function setDefault($key, $value = 0)
    {
        if ($this->isEmpty()) {
            $this[$key] = $value;
        }

        try {
            return $this[$key];
        } catch (OutOfBoundsException $e) {
            $this[$key] = $value;
        }

        return $value;
    }
}
