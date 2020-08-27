<?php
namespace GtaExternal;

const GTA_MODULE = "GTA5.exe";

const EDITION_STEAM = 0;
const EDITION_SOCIAL_CLUB = 1;
const EDITION_EPIC_GAMES = 2;

class GtaExternal
{
	public Pointer $base;
	public int $edition;

	function __construct()
	{
		$process_id = CppInterface::get_process_id(GTA_MODULE);
		if($process_id == -1)
		{
			die("GTA isn't open?\n");
		}
		$this->base = new Pointer($process_id, CppInterface::get_module_base($process_id, GTA_MODULE));
		if(CppInterface::get_module_base($process_id, "steam_api64.dll") != 0)
		{
			$this->edition = EDITION_STEAM;
		}
		else if(is_dir(dirname(CppInterface::get_module_path($process_id, GTA_MODULE))."/.egstore/"))
		{
			$this->edition = EDITION_EPIC_GAMES;
		}
		else
		{
			$this->edition = EDITION_SOCIAL_CLUB;
		}
	}

	function getEditionName() : string
	{
		switch($this->edition)
		{
			case EDITION_STEAM:
				return "Steam";

			case EDITION_EPIC_GAMES:
				return "Epic Games";
		}
		return "Social Club";
	}

	function getModule(string $module = GTA_MODULE) : Module
	{
		return new Module($this->base, $module);
	}

	function getEditionOffset(int $steam_offset, int $sc_offset, int $egs_offset) : Pointer
	{
		switch($this->edition)
		{
			case EDITION_STEAM:
				return $this->base->add($steam_offset);

			case EDITION_EPIC_GAMES:
				return $this->base->add($egs_offset);
		}
		return $this->base->add($sc_offset);
	}

	function getPedFactory() : Pointer
	{
		return $this->getEditionOffset(0x24CD000, 0x24C8858, 0x24C8858)->dereference();
	}

	function getPlayerPed() : PedPointer
	{
		return new PedPointer($this->base->process_id, $this->getPedFactory()->add(8)->dereference()->address);
	}
}
