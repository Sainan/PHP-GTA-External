<?php
namespace GtaExternal;

class Module
{
	public Pointer $base;
	public int $size;

	function __construct(Pointer $base, string $module)
	{
		$this->base = $base;
		$this->size = CppInterface::get_module_size($base->process_id, $module);
	}
}
