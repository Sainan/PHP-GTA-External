<?php
namespace GtaExternal;

class Vector3
{
	public float $x;
	public float $y;
	public float $z;

	function __construct(float $x, float $y, float $z)
	{
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
	}

	static function fromPointer(Pointer $pointer) : Vector3
	{
		return new Vector3($pointer->readFloat(), $pointer->add(4)->readFloat(), $pointer->add(8)->readFloat());
	}
}
