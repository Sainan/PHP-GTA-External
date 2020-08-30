<?php
namespace V\Pointer;
use V\Handle\ProcessHandle;
class NavigationPointer extends Pointer
{
	function __construct(ProcessHandle $processHandle, int $address)
	{
		parent::__construct($processHandle, $address);
	}

	function getRotation() : Vector3Pointer
	{
		return new Vector3Pointer($this->processHandle, $this->add(0x30)->address);
	}

	function getPosition() : Vector3Pointer
	{
		return new Vector3Pointer($this->processHandle, $this->add(0x50)->address);
	}
}
