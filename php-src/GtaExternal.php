<?php
namespace GtaExternal;

require_once __DIR__."/CppInterface.php";
require_once __DIR__."/Pointer.php";

const EDITION_STEAM = 0;
const EDITION_SOCIAL_CLUB = 1;
const EDITION_EPIC_GAMES = 2;

class GtaExternal
{
	public $base;
	public $edition;

	function __construct()
	{
		$this->base = new Pointer("GTA5.exe", CppInterface::getModuleBase("GTA5.exe"));
		if($this->base->isNullptr())
		{
			die("GTA isn't open?\n");
		}
		if(CppInterface::getModuleBase("GTA5.exe", "steam_api64.dll") != 0)
		{
			$this->edition = EDITION_STEAM;
		}
		else if(is_dir(dirname(CppInterface::getModulePath("GTA5.exe"))."/.egstore/"))
		{
			$this->edition = EDITION_EPIC_GAMES;
		}
		else
		{
			$this->edition = EDITION_SOCIAL_CLUB;
		}
	}

	function getEditionOffset(int $steam_offset, int $sc_offset, int $egs_offset) : Pointer
	{
		switch($this->edition)
		{
			case EDITION_STEAM:
				return $this->base->add($steam_offset);

			case EDITION_SOCIAL_CLUB:
				return $this->base->add($sc_offset);

			case EDITION_EPIC_GAMES:
				return $this->base->add($egs_offset);
		}
	}

	function getPedFactory() : Pointer
	{
		// To update this:
		// 1. Get a dump for the GTA edition in question using x64dbg's built-in Scylla plugin
		// 2. Open the dump in IDA
		// 3. Binary search for 48 8B 05 ? ? ? ? 48 8B 48 08 48 85 C9 74 52 8B 81
		// 4. Double-click the QWORD that is being moved into RAX
		// 5. Subtract the base address from the hex value after ".data:"
		// 6. You've made the difference, now save it:
		return $this->getEditionOffset(0x24CD000, 0x24C8858, 0x24C8858)->dereference();
	}

	function getPlayerPed() : Pointer
	{
		return $this->getPedFactory()->add(8)->dereference();
	}
}
