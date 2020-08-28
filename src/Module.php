<?php
namespace GtaExternal;
use GtaExternal\Pointer\Pointer;
class Module
{
	public int $process_id;
	public Pointer $base;
	public int $size;

	function __construct(int $process_id, Pointer $base, string $module)
	{
		$this->process_id = $process_id;
		$this->base = $base;
		$this->size = CppInterface::get_module_size($process_id, $module);
	}

	function getOffsetTo(Pointer $pointer) : int
	{
		return $pointer->address - $this->base->address;
	}
}
