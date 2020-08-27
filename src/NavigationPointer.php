<?php
namespace GtaExternal;

class NavigationPointer extends Pointer
{
	function __construct(int $process_id, int $address)
	{
		parent::__construct($process_id, $address);
	}

	function getRotation() : Vector3
	{
		return Vector3::fromPointer($this->add(0x30));
	}

	function getPosition() : Vector3
	{
		return Vector3::fromPointer($this->add(0x50));
	}
}
