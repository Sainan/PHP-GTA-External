<?php
namespace GtaExternal\Pointer;
use GtaExternal\Handle;
class NavigationPointer extends Pointer
{
	function __construct(Handle $handle, int $address)
	{
		parent::__construct($handle, $address);
	}

	function getRotation() : Vector3Pointer
	{
		return new Vector3Pointer($this->handle, $this->add(0x30)->address);
	}

	function getPosition() : Vector3Pointer
	{
		return new Vector3Pointer($this->handle, $this->add(0x50)->address);
	}
}
