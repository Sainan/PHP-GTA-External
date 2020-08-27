<?php /** @noinspection PhpUndefinedMethodInspection */
namespace GtaExternal;
use FFI;

class CppInterface
{
	public static ?FFI $cpp_api;

	public static function get_process_id(string $exe_file) : int
	{
		return self::$cpp_api->get_process_id($exe_file);
	}

	public static function get_module_base(int $process_id, string $module) : int
	{
		return self::$cpp_api->get_module_base($process_id, $module);
	}

	public static function get_module_size(int $process_id, string $module) : int
	{
		return self::$cpp_api->get_module_size($process_id, $module);
	}

	public static function get_module_path(int $process_id, string $module) : string
	{
		return self::$cpp_api->get_module_path($process_id, $module);
	}

	public static function read_bytes(int $process_id, int $address, int $bytes) : string
	{
		return self::$cpp_api->read_bytes($process_id, $address, $bytes);
	}

	public static function write_bytes(int $process_id, int $address, string $hex_data) : void
	{
		self::$cpp_api->write_bytes($process_id, $address, $hex_data);
	}
}

$cwd = getcwd();
chdir(__DIR__);
CppInterface::$cpp_api = FFI::load("cpp_api.h");
chdir($cwd);
