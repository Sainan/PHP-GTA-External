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
		for(; $pointer->address < $gta_end; $pointer->address++)
		{
			if($pointer->readByte() == $this->pattern_arr[$pattern_matches])
			{
				$pattern_matches++;
				while ($pattern_matches < $this->pattern_size && $this->pattern_arr[$pattern_matches] == -1)
				{
					$pattern_matches++;
					$pointer->address++;
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
		}
		return null;
	}
}

