<?php
namespace V\Pointer;
use V\Handle\ProcessHandle;
class Vector3Pointer extends Pointer
{
	function __construct(ProcessHandle $processHandle, int $address)
	{
		parent::__construct($processHandle, $address);
	}

	function bufferXYZ()
	{
		$this->ensureBuffer(12);
	}

	function readX() : float
	{
		return $this->readFloat();
	}

	function readY() : float
	{
		return $this->add(4)->readFloat();
	}

	function readZ() : float
	{
		return $this->add(8)->readFloat();
	}
}
