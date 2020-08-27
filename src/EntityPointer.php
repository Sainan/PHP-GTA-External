<?php
namespace GtaExternal;

class EntityPointer extends Pointer
{
	function __construct(int $process_id, int $address)
	{
		parent::__construct($process_id, $address);
	}

	function getNavigation() : NavigationPointer
	{
		return new NavigationPointer($this->process_id, $this->add(0x30)->dereference()->address);
	}
}
