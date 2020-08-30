<?php
namespace V;
use FFI;
use V\
{Handle\ProcessHandle, Pointer\Pointer};
class Module
{
	public ProcessHandle $processHandle;
	public string $name;
	public Pointer $base;
	public int $size;
	public string $path;

	/** @noinspection PhpUndefinedFieldInspection */
	function __construct(ProcessHandle $processHandle, string $name)
	{
		$this->processHandle = $processHandle;
		$this->name = $name;
		$module_snapshot = Kernel32::CreateToolhelp32Snapshot(Kernel32::TH32CS_SNAPMODULE | Kernel32::TH32CS_SNAPMODULE32, $processHandle->process_id);
		if(!$module_snapshot->isValid())
		{
			throw new Kernel32Exception("Failed to create module snapshot");
		}
		$module_entry = Kernel32::$ffi->new("MODULEENTRY32");
		$module_entry->dwSize = FFI::sizeof($module_entry);
		if(!Kernel32::Module32First($module_snapshot, $module_entry))
		{
			throw new Kernel32Exception("Failed to get module list");
		}
		do
		{
			if(FFI::string($module_entry->szModule) == $name)
			{
				$this->base = new Pointer($processHandle, $module_entry->modBaseAddr);
				$this->size = $module_entry->modBaseSize;
				$this->path = FFI::string($module_entry->szExePath);
				break;
			}
		}
		while(Kernel32::Module32Next($module_snapshot, $module_entry));
	}

	function isValid() : bool
	{
		return $this->base instanceof Pointer;
	}

	function getOffsetTo(Pointer $pointer) : int
	{
		return $pointer->address - $this->base->address;
	}
}
