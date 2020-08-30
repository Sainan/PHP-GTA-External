<?php
namespace V;
use PWH\
{Handle\ProcessHandle, Pointer};
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
