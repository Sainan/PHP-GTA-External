<?php
namespace GtaExternal;

require_once __DIR__."/CppInterface.php";
require_once __DIR__."/Pointer.php";

class GtaExternal
{
	public $base;

	function __construct()
	{
		$this->base = new Pointer("GTA5.exe", CppInterface::getModuleBase("GTA5.exe"));
		if($this->base->isNullptr())
		{
			die("GTA isn't open?\n");
		}
		if(CppInterface::getModuleBase("GTA5.exe", "steam_api64.dll") == 0)
		{
			die("Sorry, only Steam edition is supported right now.\n");
		}
	}

	function getEditionOffset(int $steam_offset, int $sc_offset = 0, int $egs_offset = 0) : Pointer
	{
		return $this->base->add($steam_offset);
	}

	function getPedFactory() : Pointer
	{
		// To update this:
		// 1. Get a dump for the GTA edition in question using x64dbg's built-in Scylla plugin
		// 2. Open the dump in IDA
		// 3. Pattern scan for 48 8B 05 ? ? ? ? 48 8B 48 08 48 85 C9 74 52 8B 81
		// 4. Double-click the DWORD that is being moved into RAX
		// 5. Subtract the base address from the hex value after ".data:"
		// 6. You've made the difference, now save it:
		return $this->getEditionOffset(0x24B0C50)->dereference();
	}

	function getPlayerPed() : Pointer
	{
		return $this->getPedFactory()->add(8)->dereference()->add(4);
	}
}
