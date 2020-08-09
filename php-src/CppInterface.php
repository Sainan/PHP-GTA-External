<?php
namespace GtaExternal;

class CppInterface
{
	static function getModuleBase(string $process, ?string $module = null) : int
	{
		if($module == null)
		{
			$module = $process;
		}
		return intval(shell_exec("\"".__DIR__."/../cpp-bin/get-module-base.exe\" \"$process\" \"$module\""));
	}

	static function getModulePath(string $process, ?string $module = null): string
	{
		if($module == null)
		{
			$module = $process;
		}
		return shell_exec("\"".__DIR__."/../cpp-bin/get-module-path.exe\" \"$process\" \"$module\"");
	}

	static function readBytes(string $process, int $address, int $bytes) : string
	{
		return shell_exec("\"".__DIR__."/../cpp-bin/read-bytes.exe\" \"$process\" \"$address\" \"$bytes\"");
	}

	static function writeBytes(string $process, int $address, string $hex_encoded_data) : void
	{
		shell_exec("\"".__DIR__."/../cpp-bin/write-bytes.exe\" \"$process\" \"$address\" \"$hex_encoded_data\"");
	}
}
