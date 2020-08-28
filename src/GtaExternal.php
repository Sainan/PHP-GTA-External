<?php
namespace GtaExternal;
use GtaExternal\Pointer\
{PedPointer, Pointer};

const GTA_MODULE = "GTA5.exe";

class GtaExternal
{
	public int $process_id;
	public Pointer $base;
	public ?Pointer $ped_factory_ptr = null;

	function __construct()
	{
		$this->process_id = CppInterface::get_process_id(GTA_MODULE);
		if($this->process_id == -1)
		{
			die("GTA isn't open?\n");
		}
		$this->base = new Pointer(CppInterface::open_process($this->process_id), CppInterface::get_module_base($this->process_id, GTA_MODULE));
	}

	function getEditionName() : string
	{
		if(CppInterface::get_module_base($this->process_id, "steam_api64.dll") != 0)
		{
			return "Steam";
		}
		if(is_dir(dirname(CppInterface::get_module_path($this->process_id, GTA_MODULE))."/.egstore/"))
		{
			return "Epic Games";
		}
		return "Social Club";
	}

	function getModule(string $module = GTA_MODULE) : Module
	{
		return new Module($this->process_id, $this->base, $module);
	}

	function ensurePedFactoryPtr() : void
	{
		if(!$this->ped_factory_ptr instanceof Pointer)
		{
			echo "Looking for PedFactory... ";
			$module = $this->getModule();
			$this->ped_factory_ptr = (new Pattern("48 8B 05 ? ? ? ? 48 8B 48 08 48 85 C9 74 52 8B 81"))->scan($module);
			if(!$this->ped_factory_ptr instanceof Pointer)
			{
				die("Pattern not found. :(\n");
			}
			$this->ped_factory_ptr = $this->ped_factory_ptr->add(3)->rip();
			echo "Found at ".GTA_MODULE."+".dechex($module->getOffsetTo($this->ped_factory_ptr))." ({$this->ped_factory_ptr})\n";
		}
	}

	function getPedFactory() : Pointer
	{
		$this->ensurePedFactoryPtr();
		return $this->ped_factory_ptr->dereference();
	}

	function getPlayerPed() : PedPointer
	{
		return new PedPointer($this->base->handle, $this->getPedFactory()->add(8)->dereference()->address);
	}
}
