<?php
namespace V;
use PWH\Handle\ProcessHandle;
class PedPointer extends EntityPointer
{
	const OFFSET_HEALTH = 0x0280;
	const OFFSET_ARMOR = 0x14E0;

	function __construct(ProcessHandle $processHandle, int $address)
	{
		parent::__construct($processHandle, $address);
	}

	function getHealth() : float
	{
		return $this->add(self::OFFSET_HEALTH)->readFloat();
	}

	function setHealth(float $health) : PedPointer
	{
		$this->add(self::OFFSET_HEALTH)->writeFloat($health);
		return $this;
	}

	function getArmor() : float
	{
		return $this->add(self::OFFSET_ARMOR)->readFloat();
	}

	function setArmor(float $armor) : PedPointer
	{
		$this->add(self::OFFSET_ARMOR)->writeFloat($armor);
		return $this;
	}
}
