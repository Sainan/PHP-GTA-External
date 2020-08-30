<?php
namespace V;
use FFI;
use V\Pointer\
{PedPointer, Pointer};
const GTA_MODULE = "GTA5.exe";

const PATTERN_SCAN_RESULTS_CACHE_JSON_PATH = __DIR__."/../pattern_scan_results_cache.json";

class GTA
{
	public Module $module;
	private array $pattern_scan_results_cache = [];
	private array $pattern_scan_results = [];

	function __construct(int $desired_access = PROCESS_VM_OPERATION | PROCESS_VM_READ | PROCESS_VM_WRITE, int $process_id = -1)
	{
		if($process_id == -1)
		{
			$process_id = self::getGtaProcessId();
			if($process_id == -1)
			{
				die("GTA V isn't open?\n");
			}
		}
		$this->module = new Module(Kernel32::OpenProcess($process_id, $desired_access), GTA_MODULE);
	}

	static function tryConstruct(int $desired_access = PROCESS_VM_OPERATION | PROCESS_VM_READ | PROCESS_VM_WRITE) : ?GTA
	{
		$process_id = self::getGtaProcessId();
		if($process_id == -1)
		{
			return null;
		}
		return new GTA($desired_access, $process_id);
	}

	/** @noinspection PhpUndefinedFieldInspection */
	static function getGtaProcessId() : int
	{
		$process_snapshot = Kernel32::CreateToolhelp32Snapshot(TH32CS_SNAPPROCESS, 0);
		if($process_snapshot->isValid())
		{
			$process_entry = Kernel32::$ffi->new("PROCESSENTRY32");
			$process_entry->dwSize = FFI::sizeof($process_entry);
			if(Kernel32::Process32First($process_snapshot, $process_entry))
			{
				do
				{
					if(FFI::string($process_entry->szExeFile) == GTA_MODULE)
					{
						return $process_entry->th32ProcessID;
					}
				}
				while(Kernel32::Process32Next($process_snapshot, $process_entry));
			}
			else
			{
				throw new Kernel32Exception();
			}
		}
		return -1;
	}

	function initPatternScanResultsCache() : void
	{
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
						$this->pattern_scan_results[$pattern_name] = $this->module->base->add($this->pattern_scan_results_cache[$pattern_name]);
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
		if($this->getModule("steam_api64.dll")->isValid())
		{
			return "Steam";
		}
		if(is_dir(dirname($this->getModule()->path)."/.egstore/"))
		{
			return "Epic Games";
		}
		return "Social Club";
	}

	function getModule(string $module = GTA_MODULE) : Module
	{
		return $module == GTA_MODULE ? $this->module : new Module($this->module->processHandle, $module);
	}

	function allocate(int $bytes) : Pointer
	{
		return Kernel32::VirtualAllocEx($this->module->processHandle, $bytes);
	}

	function getPatternScanResult(string $pattern_name, callable $get_pattern_func, ?callable $process_pointer_func = null) : Pointer
	{
		if(!array_key_exists($pattern_name, $this->pattern_scan_results))
		{
			echo "Looking for {$pattern_name}... ";
			$module = $this->getModule();
			$this->pattern_scan_results[$pattern_name] = ($get_pattern_func())->scan($module);
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
		$pointer = $this->getPatternScanResult("Online Version", function() : Pattern
		{
			return Pattern::ida("4C 8D 05 ? ? ? ? 48 8D 15 ? ? ? ? 48 8B C8 E8 ? ? ? ? 48 8D 15 ? ? ? ? 48 8D 4C 24 20 E8");
		}, function(Pointer $pointer) : Pointer
		{
			return $pointer->add(3)->rip();
		});
		$pointer->ensureBuffer(5);
		return $pointer->readString();
	}

	function getPedFactory() : Pointer
	{
		return $this->getPatternScanResult("Ped Factory", function() : Pattern
		{
			return Pattern::ida("48 8B 05 ? ? ? ? 48 8B 48 08 48 85 C9 74 52 8B 81");
		}, function(Pointer $pointer) : Pointer
		{
			return $pointer->add(3)->rip();
		})->dereference();
	}

	function getPlayerPed() : PedPointer
	{
		return new PedPointer($this->module->base->processHandle, $this->getPedFactory()->add(8)->dereference()->address);
	}

	function getScriptGlobal(int $global) : Pointer
	{
		return $this->getPatternScanResult("Script Globals", function() : Pattern
		{
			return Pattern::ida("48 8D 15 ? ? ? ? 4C 8B C0 E8 ? ? ? ? 48 85 FF 48 89 1D");
		}, function(Pointer $pointer) : Pointer
		{
			return $pointer->add(3)->rip();
		})->add((($global >> 0x12) & 0x3F) * 8)->dereference()->add(($global & 0x3FFFF) * 8);
	}
}
