<?php
namespace GtaExternal;
use GtaExternal\Pointer\Pointer;
class Pattern
{
	public array $pattern_arr;
	public int $pattern_size;

	function __construct(string $pattern)
	{
		$this->pattern_arr = explode(" ", $pattern);
		$this->pattern_size = count($this->pattern_arr);
		for($i = 0; $i < $this->pattern_size; $i++)
		{
			$this->pattern_arr[$i] = $this->pattern_arr[$i] == "?" ? -1 : hexdec($this->pattern_arr[$i]);
		}
	}

	function scan(Module $module) : ?Pointer
	{
		$pattern_matches = 0;
		$gta_end = $module->base->address + $module->size - 1;
		$pointer = clone $module->base;
		$buffer_i = $buffer_size = CppInterface::buffer_size();
		while($pointer->address < $gta_end)
		{
			if($buffer_i >= $buffer_size)
			{
				CppInterface::process_read_bytes($pointer->handle, $pointer->address, $buffer_size);
				$buffer_i = 0;
			}
			if(CppInterface::buffer_read_byte($buffer_i) == $this->pattern_arr[$pattern_matches])
			{
				$pattern_matches++;
				while ($pattern_matches < $this->pattern_size && $this->pattern_arr[$pattern_matches] == -1)
				{
					$pattern_matches++;
					$pointer->address++;
					$buffer_i++;
				}
				if($pattern_matches >= $this->pattern_size)
				{
					return $pointer->subtract($this->pattern_size - 1);
				}
			}
			else
			{
				$pattern_matches = 0;
			}
			$pointer->address++;
			$buffer_i++;
		}
		return null;
	}
}

