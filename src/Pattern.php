<?php
namespace V;
use V\Pointer\Pointer;
class Pattern
{
	private array $pattern_arr;
	public int $pattern_size;

	function __construct(array $pattern_arr)
	{
		$this->pattern_arr = $pattern_arr;
		$this->pattern_size = count($this->pattern_arr);
	}

	static function isIdaPatternString(string $pattern) : bool
	{
		return preg_match('/((\?|[0-9a-f]{2}) )+[0-9a-f]{2}/i', $pattern) === 1;
	}

	static function ida(string $pattern) : Pattern
	{
		$pattern_arr = explode(" ", $pattern);
		foreach($pattern_arr as $i => $c)
		{
			$pattern_arr[$i] = $c == "?" ? -1 : hexdec($c);
		}
		return new Pattern($pattern_arr);
	}

	static function binary(string $pattern, ?string $mask = null) : Pattern
	{
		$pattern_arr = array_map("ord", str_split($pattern));
		if(is_string($mask))
		{
			foreach(str_split($mask) as $i => $c)
			{
				if($c == "?")
				{
					$pattern_arr[$i] = -1;
				}
			}
		}
		return new Pattern($pattern_arr);
	}

	static function escapedBinary(string $pattern, ?string $mask = null) : Pattern
	{
		$arr = explode("\\x", $pattern);
		array_shift($arr);
		return Pattern::binary(join("", array_map("chr", array_map("hexdec", $arr))), $mask);
	}

	function scan(Module $module, ?Pointer $start = null) : ?Pointer
	{
		$pattern_matches = 0;
		$module_end = $module->base->address + $module->size - 1;
		$pointer = $start ?? clone $module->base;
		for(; $pointer->address < $module_end; $pointer->address++)
		{
			if(!$pointer->isBuffered())
			{
				$pointer->buffer($module_end - $pointer->address);
			}
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

	function scanAll(Module $module, callable $on_match) : void
	{
		$module_end = $module->base->address + $module->size - 1;
		$pointer = clone $module->base;
		while($pointer->address < $module_end)
		{
			$res = $this->scan($module, $pointer);
			if(!$res instanceof Pointer)
			{
				break;
			}
			$pointer = $res->add(1);
			$on_match($res);
		}
	}

	function toPatternString() : string
	{
		$str = "[";
		foreach($this->pattern_arr as $i)
		{
			$str .= $i.", ";
		}
		return rtrim($str, ", ")."]";
	}

	function toIdaPatternString() : string
	{
		$str = "";
		foreach($this->pattern_arr as $i)
		{
			$str .= $i == -1 ? "?" : dechex($i);
			$str .= " ";
		}
		return rtrim($str, " ");
	}

	function __toString() : string
	{
		return $this->toIdaPatternString();
	}

	function toBinaryPatternString() : string
	{
		$pattern = "";
		$mask = "";
		foreach($this->pattern_arr as $i)
		{
			$pattern .= "\\x".str_pad(dechex($i == -1 ? 0 : $i), 2, "0", STR_PAD_LEFT);
			$mask .= $i == -1 ? "?" : "x";
		}
		$pattern = "\"".$pattern."\"";
		if(strpos($mask, "?") !== false)
		{
			$pattern .= ", \"$mask\"";
		}
		return $pattern;
	}
}

