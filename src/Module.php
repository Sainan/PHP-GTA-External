<?php
namespace V;
use V\Pointer\Pointer;
class Module
{
	public int $process_id;
	public string $name;
	public Pointer $base;
	public int $size;

	function __construct(int $process_id, string $name, ?Pointer $base = null)
	{
		$this->process_id = $process_id;
		$this->base = $base ?? NativeHelper::get_module_base($this->process_id, $name);
		$this->name = $name;
		$this->size = NativeHelper::get_module_size($process_id, $name);
	}

	function isValid() : bool
	{
		return $this->base->address != 0 && $this->size != 0;
	}

	function getPath() : string
	{
		return NativeHelper::get_module_path($this->process_id, $this->name);
	}

	function getOffsetTo(Pointer $pointer) : int
	{
		return $pointer->address - $this->base->address;
	}
}
