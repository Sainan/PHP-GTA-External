<?php /** @noinspection PhpUndefinedMethodInspection */
namespace V;
use FFI;

const PROCESS_VM_OPERATION = 0x0008;
const PROCESS_VM_READ = 0x0010;
const PROCESS_VM_WRITE = 0x0020;

class Kernel32
{
	public static FFI $ffi;

	public static function OpenProcess(int $process_id, int $desired_access = PROCESS_VM_OPERATION | PROCESS_VM_READ | PROCESS_VM_WRITE) : Handle
	{
		return new Handle(self::$ffi->OpenProcess($desired_access, false, $process_id));
	}

	public static function CloseHandle(int $handle)
	{
		return self::$ffi->CloseHandle($handle);
	}
}

Kernel32::$ffi = FFI::cdef(<<<EOC
uint64_t OpenProcess(uint32_t, bool, uint32_t);
int CloseHandle(uint64_t);
EOC, "kernel32");