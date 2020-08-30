<?php /** @noinspection PhpUndefinedMethodInspection */
namespace V;
use FFI;
class NativeHelper
{
	public static FFI $ffi;
	public static int $buffer_size = 0;
	public static int $buffer_address_start = 0;
	public static int $buffer_address_end = 0;

	public static function get_process_id(string $exe_file) : int
	{
		return self::$ffi->get_process_id($exe_file);
	}

	public static function get_module_base(int $process_id, string $module) : int
	{
		return self::$ffi->get_module_base($process_id, $module);
	}

	public static function get_module_size(int $process_id, string $module) : int
	{
		return self::$ffi->get_module_size($process_id, $module);
	}

	public static function get_module_path(int $process_id, string $module) : string
	{
		$path = self::$ffi->get_module_path($process_id, $module);
		self::$buffer_address_start += strlen($path) + 1;
		return $path;
	}

	public static function buffer_read_byte(int $index) : int
	{
		return self::$ffi->buffer_read_byte($index);
	}

	public static function buffer_write_byte(int $index, int $value) : void
	{
		self::$buffer_address_start = self::$buffer_address_end = 0;
		self::$ffi->buffer_write_byte($index, $value);
	}


	public static function process_read_bytes(Handle $handle, int $address, int $bytes) : void
	{
		if($bytes > self::$buffer_size)
		{
			$bytes = self::$buffer_size;
		}
		self::$buffer_address_start = $address;
		self::$buffer_address_end = $address + $bytes;
		self::$ffi->process_read_bytes($handle->handle, $address, $bytes);
	}

	public static function process_write_bytes(Handle $handle, int $address, int $bytes) : void
	{
		self::$ffi->process_write_bytes($handle->handle, $address, $bytes);
	}
}

$cwd = getcwd();
chdir(__DIR__);
NativeHelper::$ffi = FFI::load("native_helper.h");
NativeHelper::$buffer_size = NativeHelper::$ffi->buffer_size();
chdir($cwd);
