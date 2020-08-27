<?php
namespace GtaExternal;

const OFFSET_PED_HEALTH = 0x0280;
const OFFSET_PED_ARMOR = 0x14E0;

class PedPointer extends EntityPointer
{
	function __construct(int $process_id, int $address)
	{
		parent::__construct($process_id, $address);
	}

	function getHealth() : float
	{
		return $this->add(OFFSET_PED_HEALTH)->readFloat();
	}

	function setHealth(float $health) : PedPointer
	{
		$this->add(OFFSET_PED_HEALTH)->writeFloat($health);
		return $this;
	}

	function getArmor() : float
	{
		return $this->add(OFFSET_PED_ARMOR)->readFloat();
	}

	function setArmor(float $armor) : PedPointer
	{
		$this->add(OFFSET_PED_ARMOR)->writeFloat($armor);
		return $this;
	}
}