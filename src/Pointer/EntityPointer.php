<?php
namespace V\Pointer;
use V\Handle\ProcessHandle;
class EntityPointer extends Pointer
{
	function __construct(ProcessHandle $processHandle, int $address)
	{
		parent::__construct($processHandle, $address);
	}

	function getNavigation() : NavigationPointer
	{
		return new NavigationPointer($this->processHandle, $this->add(0x30)->dereference()->address);
	}
}
