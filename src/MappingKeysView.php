<?php
namespace Headbanger;

class MappingKeysView extends BaseSet
{
    use MappingView;
	/**
	 *
	 */
	public function contains($key)
	{
		return $this->mapping->contains($key);
	}
}
