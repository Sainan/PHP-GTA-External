<?php
namespace V;
use PWH\
{AbstractProcess, Kernel32, Pattern, Pointer};
class GTA extends AbstractProcess
{
	function __construct(int $desired_access = Kernel32::PROCESS_VM_OPERATION | Kernel32::PROCESS_VM_READ | Kernel32::PROCESS_VM_WRITE)
	{
		parent::__construct("GTA5.exe", $desired_access);
	}

	function getPatternResultsCacheJsonFilePath(): string
	{
		return __DIR__."/../pattern_scan_results_cache.json";
	}

	function getUniqueVersionAndEditionName() : string
	{
		return $this->getEditionName().", Build ".$this->getBuildVersion().", Online ".$this->getOnlineVersion();
	}

	function getEditionName() : string
	{
		if($this->getModule("steam_api64.dll")->isValid())
		{
			return "Steam";
		}
		if(is_dir(dirname($this->module->path)."/.egstore/"))
		{
			return "Epic Games";
		}
		return "Social Club";
	}

	function getVersionsPointer() : Pointer
	{
		return $this->getPatternScanResult("Version Numbers", function() : Pattern
		{
			return Pattern::ida("4C 8D 05 ? ? ? ? 48 8D 15 ? ? ? ? 48 8B C8 E8 ? ? ? ? 48 8D 15 ? ? ? ? 48 8D 4C 24 20 E8");
		}, function(Pointer $pointer) : Pointer
		{
			return $pointer;
		});
	}

	function getBuildVersion() : string
	{
		$pointer = $this->getVersionsPointer()->subtract(165)->rip();
		$pointer->ensureBuffer(7);
		return $pointer->readString();
	}

	function getOnlineVersion() : string
	{
		$pointer = $this->getVersionsPointer()->add(3)->rip();
		$pointer->ensureBuffer(5);
		return $pointer->readString();
	}

	function getPedFactory() : Pointer
	{
		return $this->getPatternScanResult("Ped Factory", function() : Pattern
		{
			return Pattern::ida("48 8B 05 ? ? ? ? 48 8B 48 08 48 85 C9 74 52 8B 81");
		}, function(Pointer $pointer) : Pointer
		{
			return $pointer->add(3)->rip();
		})->dereference();
	}

	function getPlayerPed() : PedPointer
	{
		return new PedPointer($this->module->base->processHandle, $this->getPedFactory()->add(8)->dereference()->address);
	}

	function getScriptGlobal(int $global) : Pointer
	{
		return $this->getPatternScanResult("Script Globals", function() : Pattern
		{
			return Pattern::ida("48 8D 15 ? ? ? ? 4C 8B C0 E8 ? ? ? ? 48 85 FF 48 89 1D");
		}, function(Pointer $pointer) : Pointer
		{
			return $pointer->add(3)->rip();
		})->add((($global >> 0x12) & 0x3F) * 8)->dereference()->add(($global & 0x3FFFF) * 8);
	}
}
