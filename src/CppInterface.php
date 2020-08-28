<?php /** @noinspection PhpUndefinedMethodInspection */
namespace GtaExternal;
use FFI;
class CppInterface
{
	public static ?FFI $cpp_api;
	public static int $buffer_size = 0;
	public static int $buffer_address_start = 0;
	public static int $buffer_address_end = 0;

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
		$path = self::$cpp_api->get_module_path($process_id, $module);
		self::$buffer_address_start += strlen($path) + 1;
		return $path;
	}


	public static function open_process(int $process_id) : Handle
	{
		return new Handle(self::$cpp_api->open_process($process_id));
	}

	public static function close_handle(int $handle)
	{
		return self::$cpp_api->close_handle($handle);
	}


	public static function buffer_read_byte(int $index) : int
	{
		return self::$cpp_api->buffer_read_byte($index);
	}

	public static function buffer_write_byte(int $index, int $value) : void
	{
		self::$buffer_address_start = self::$buffer_address_end = 0;
		self::$cpp_api->buffer_write_byte($index, $value);
	}


	public static function process_read_bytes(Handle $handle, int $address, int $bytes) : void
	{
		if($bytes > self::$buffer_size)
		{
			$bytes = self::$buffer_size;
		}
		self::$buffer_address_start = $address;
		self::$buffer_address_end = $address + $bytes;
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
CppInterface::$buffer_size = CppInterface::$cpp_api->buffer_size();
