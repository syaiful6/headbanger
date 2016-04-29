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
    public static function fromIterable($iterable)
    {
        return new Set($iterable);
    }

	/**
     *
     */
    public function contains($item)
    {
        list($key, $value) = $item;
        $except = false;
        try {
            $v = $this->mapping[$key];
        } catch (OutOfBoundsException $e) {
            $except = true;

            return false;
        } catch(UnderflowException $e) {
            $except = true;

            return false;
        }
        if (! $except) {
            return $v === $value;
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
