<?php
namespace Headbanger;

use OutOfBoundsException;
use UnderflowException;

class MappingItemsView extends BaseSet
{
    use MappingView;

    /**
     *
     */
    protected static function fromIterable($iterable)
    {
        return new Set($iterable);
    }

    /**
     *
     */
    public function contains($item)
    {
        list($key, $value) = $item;

        try {
            $v = $this->mapping[$key];

            return $v === $value;
        } catch (OutOfBoundsException $e) {

            return false;
        } catch (UnderflowException $e) {

            return false;
        }
    }

    /**
     *
     */
    public function getIterator()
    {
        $iterator = parent::getIterator();
        foreach ($iterator as $key) {
            yield [$key, $this->mapping[$key]];
        }
    }
}
