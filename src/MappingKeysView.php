<?php
namespace Headbanger;

class MappingKeysView extends MappingView
{
	/**
	 *
	 */
	public function contains($key)
	{
		return $this->mapping->contains($key);
	}
}
