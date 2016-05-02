<?php

namespace Headbanger;

class MappingKeysView extends BaseSet
{
    use MappingView;

    /**
     *
     */
    protected static function fromIterable($it)
    {
        return new Set($it);
    }

    /**
     *
     */
    public function contains($key)
    {
        return $this->mapping->contains($key);
    }
}
