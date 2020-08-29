<?php
namespace V\Pointer;
use V\Handle;
class EntityPointer extends Pointer
{
	function __construct(Handle $handle, int $address)
	{
		parent::__construct($handle, $address);
	}

	function getNavigation() : NavigationPointer
	{
		return new NavigationPointer($this->handle, $this->add(0x30)->dereference()->address);
	}
}
