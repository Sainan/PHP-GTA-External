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


	public static function open_process(int $process_id) : Handle
	{
		return new Handle(self::$cpp_api->open_process($process_id));
	}

	public static function close_handle(int $handle)
	{
		return self::$cpp_api->close_handle($handle);
	}


	public static function buffer_size() : int
	{
		return self::$cpp_api->buffer_size();
	}

	public static function buffer_read_byte(int $index) : int
	{
		return self::$cpp_api->buffer_read_byte($index);
	}

	public static function buffer_write_byte(int $index, int $value) : void
	{
		self::$cpp_api->buffer_write_byte($index, $value);
	}


	public static function process_read_byte(Handle $handle, int $address) : int
	{
		return self::$cpp_api->process_read_byte($handle->handle, $address);
	}

	public static function process_read_bytes(Handle $handle, int $address, int $bytes) : void
	{
		self::$cpp_api->process_read_bytes($handle->handle, $address, $bytes);
	}

	public static function process_write_bytes(Handle $handle, int $address, int $bytes) : void
	{
		self::$cpp_api->process_write_bytes($handle->handle, $address, $bytes);
	}
}

$cwd = getcwd();
chdir(__DIR__);
CppInterface::$cpp_api = FFI::load("cpp_api.h");
chdir($cwd);
