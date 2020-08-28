<?php
namespace GtaExternal\Pointer;
use GtaExternal\Handle;
class Vector3Pointer extends Pointer
{
	function __construct(Handle $handle, int $address)
	{
		parent::__construct($handle, $address);
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
