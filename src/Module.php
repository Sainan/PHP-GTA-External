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
		$module_snapshot = Kernel32::CreateToolhelp32Snapshot(TH32CS_SNAPMODULE | TH32CS_SNAPMODULE32, $processHandle->process_id);
		if($module_snapshot->isValid())
		{
			$module_entry = Kernel32::$ffi->new("MODULEENTRY32");
			$module_entry->dwSize = FFI::sizeof($module_entry);
			if(Kernel32::Module32First($module_snapshot, $module_entry))
			{
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
			else
			{
				throw new Kernel32Exception();
			}
		}
		else
		{
			throw new Kernel32Exception();
		}
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
