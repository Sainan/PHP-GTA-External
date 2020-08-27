<?php
namespace GtaExternal;

class Pattern
{
	public string $pattern;

	function __construct(string $pattern)
	{
		$this->pattern = strtolower($pattern);
	}

	function scan(Module $module) : ?Pointer
	{
		$pattern_arr = explode(" ", $this->pattern);
		$pattern_size = count($pattern_arr);
		$pattern_matches = 0;

		$gta_end = $module->base->address + $module->size - 1;
		for($addr = $module->base->address; $addr < $gta_end; $addr++)
		{
			if(CppInterface::read_bytes($module->base->process_id, $addr, 1) == $pattern_arr[$pattern_matches])
			{
				$pattern_matches++;
				while ($pattern_matches < $pattern_size && $pattern_arr[$pattern_matches] == "?")
				{
					$pattern_matches++;
					$addr++;
				}
				if($pattern_matches >= $pattern_size)
				{
					return new Pointer($module->base->process_id, ($addr - $pattern_size) + 1);
				}
			}
			else
			{
				$pattern_matches = 0;
			}
		}
		return null;
	}
}

