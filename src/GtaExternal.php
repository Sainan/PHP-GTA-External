<?php
namespace GtaExternal;
use GtaExternal\Pointer\
{PedPointer, Pointer};

const GTA_MODULE = "GTA5.exe";

const PATTERN_SCAN_RESULTS_CACHE_JSON_PATH = __DIR__."/../pattern_scan_results_cache.json";

class GtaExternal
{
	public int $process_id;
	public Pointer $base;
	private array $pattern_scan_results_cache = [];
	private array $pattern_scan_results = [];

	function __construct()
	{
		$this->process_id = CppInterface::get_process_id(GTA_MODULE);
		if($this->process_id == -1)
		{
			die("GTA isn't open?\n");
		}
		$this->base = new Pointer(CppInterface::open_process($this->process_id), CppInterface::get_module_base($this->process_id, GTA_MODULE));
		$online_version = $this->getOnlineVersion();
		if(file_exists(PATTERN_SCAN_RESULTS_CACHE_JSON_PATH))
		{
			$this->pattern_scan_results_cache = json_decode(file_get_contents(PATTERN_SCAN_RESULTS_CACHE_JSON_PATH), true);
			if(@$this->pattern_scan_results_cache["__edition"] === $this->getEditionName() && @$this->pattern_scan_results_cache["__version"] === $online_version)
			{
				echo "Edition and Online Version match cache, so we're using cached offsets!\n";
				foreach($this->pattern_scan_results_cache as $pattern_name => $offset)
				{
					if(substr($pattern_name, 0, 2) != "__")
					{
						$this->pattern_scan_results[$pattern_name] = $this->base->add($this->pattern_scan_results_cache[$pattern_name]);
					}
				}
				return;
			}
		}
		$this->pattern_scan_results_cache = [
			"__edition" => $this->getEditionName(),
			"__version" => $online_version,
		];
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

	function getPatternScanResult(string $pattern_name, string $pattern, ?callable $process_pointer_func = null) : Pointer
	{
		if(!array_key_exists($pattern_name, $this->pattern_scan_results))
		{
			echo "Looking for {$pattern_name}... ";
			$module = $this->getModule();
			$this->pattern_scan_results[$pattern_name] = (new Pattern($pattern))->scan($module);
			if(!$this->pattern_scan_results[$pattern_name] instanceof Pointer)
			{
				die("Pattern not found. :(\n");
			}
			if(is_callable($process_pointer_func))
			{
				$this->pattern_scan_results[$pattern_name] = $process_pointer_func($this->pattern_scan_results[$pattern_name]);
			}
			$offset = $module->getOffsetTo($this->pattern_scan_results[$pattern_name]);
			echo "Found at ".GTA_MODULE."+".dechex($offset)." (".$this->pattern_scan_results[$pattern_name].")";
			if(count($this->pattern_scan_results_cache) > 0)
			{
				$this->pattern_scan_results_cache[$pattern_name] = $offset;
				file_put_contents(PATTERN_SCAN_RESULTS_CACHE_JSON_PATH, json_encode($this->pattern_scan_results_cache));
			}
			echo "\n";
		}
		return $this->pattern_scan_results[$pattern_name];
	}

	function getOnlineVersion() : string
	{
		return $this->getPatternScanResult("Online Version", "4C 8D 05 ? ? ? ? 48 8D 15 ? ? ? ? 48 8B C8 E8 ? ? ? ? 48 8D 15 ? ? ? ? 48 8D 4C 24 20 E8", function(Pointer $pointer) : Pointer
		{
			return $pointer->add(3)->rip();
		})->readString();
	}

	function getPedFactory() : Pointer
	{
		return $this->getPatternScanResult("Ped Factory", "48 8B 05 ? ? ? ? 48 8B 48 08 48 85 C9 74 52 8B 81", function(Pointer $pointer) : Pointer
		{
			return $pointer->add(3)->rip();
		})->dereference();
	}

	function getPlayerPed() : PedPointer
	{
		return new PedPointer($this->base->handle, $this->getPedFactory()->add(8)->dereference()->address);
	}
}
