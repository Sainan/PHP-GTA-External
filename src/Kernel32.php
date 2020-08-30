<?php /** @noinspection PhpUndefinedMethodInspection */
namespace V;
use FFI;
use FFI\CData;
use V\
{Handle\Handle, Handle\ProcessHandle};

use const V\Pointer\nullptr;

const MAX_PATH = 260;

const PROCESS_VM_OPERATION = 0x0008;
const PROCESS_VM_READ = 0x0010;
const PROCESS_VM_WRITE = 0x0020;

const MAX_MODULE_NAME32 = 255;

const TH32CS_SNAPPROCESS = 0x00000002;
const TH32CS_SNAPMODULE = 0x00000008;
const TH32CS_SNAPMODULE32 = 0x00000010;

class Kernel32
{
	static FFI $ffi;

	static function GetLastError() : int
	{
		return self::$ffi->GetLastError();
	}

	static function CloseHandle(int $handle) : void
	{
		if(self::$ffi->CloseHandle($handle) == 0)
		{
			throw new Kernel32Exception();
		}
	}

	static function ReadProcessMemory(ProcessHandle $processHandle, int $base_address, CData $buffer, int $bytes)
	{
		self::$ffi->ReadProcessMemory($processHandle->handle, $base_address, FFI::addr($buffer), $bytes, nullptr);
	}

	static function WriteProcessMemory(ProcessHandle $processHandle, int $base_address, CData $buffer, int $bytes)
	{
		self::$ffi->WriteProcessMemory($processHandle->handle, $base_address, FFI::addr($buffer), $bytes, nullptr);
	}

	static function OpenProcess(int $process_id, int $desired_access) : ProcessHandle
	{
		$handle = new ProcessHandle($process_id, self::$ffi->OpenProcess($desired_access, false, $process_id), $desired_access);
		if(!$handle->isValid())
		{
			throw new Kernel32Exception();
		}
		return $handle;
	}

	static function CreateToolhelp32Snapshot(int $flags, int $process_id) : Handle
	{
		return new Handle(self::$ffi->CreateToolhelp32Snapshot($flags, $process_id));
	}

	static function Module32First(Handle $snapshot_handle, CData $process_entry) : bool
	{
		return Kernel32::$ffi->Module32First($snapshot_handle->handle, FFI::addr($process_entry));
	}

	static function Module32Next(Handle $snapshot_handle, CData $process_entry) : bool
	{
		return Kernel32::$ffi->Module32Next($snapshot_handle->handle, FFI::addr($process_entry));
	}

	static function Process32First(Handle $snapshot_handle, CData $process_entry) : bool
	{
		return Kernel32::$ffi->Process32First($snapshot_handle->handle, FFI::addr($process_entry));
	}

	static function Process32Next(Handle $snapshot_handle, CData $process_entry) : bool
	{
		return Kernel32::$ffi->Process32Next($snapshot_handle->handle, FFI::addr($process_entry));
	}
}

Kernel32::$ffi = FFI::cdef(str_replace(
	["CHAR", "BOOL", "DWORD",    "HMODULE", "HANDLE",   "SIZE_T",    "ULONG_PTR", "LONG",    "MAX_PATH", "MAX_MODULE_NAME32"],
	["char", "bool", "uint32_t", "HANDLE",  "uint64_t", "ULONG_PTR", "uint64_t",  "uint32_t", MAX_PATH ,  MAX_MODULE_NAME32 ],
	<<<EOC
// Errhandlingapi.h
DWORD GetLastError();

// Handleapi.h
BOOL CloseHandle(HANDLE hObject);

// Memoryapi.h
BOOL ReadProcessMemory(HANDLE hProcess, uint64_t lpBaseAddress, void* lpBuffer, SIZE_T nSize, uint64_t lpNumberOfBytesRead);
BOOL WriteProcessMemory(HANDLE hProcess, uint64_t lpBaseAddress, void* lpBuffer, SIZE_T nSize, uint64_t lpNumberOfBytesWritten);

// Processthreadsapi.h
HANDLE OpenProcess(DWORD dwDesiredAccess, BOOL bInheritHandle, DWORD dwProcessId);

// tlhelp32.h
typedef struct tagMODULEENTRY32 {
  DWORD   dwSize;
  DWORD   th32ModuleID;
  DWORD   th32ProcessID;
  DWORD   GlblcntUsage;
  DWORD   ProccntUsage;
uint64_t  modBaseAddr;
  DWORD   modBaseSize;
  HMODULE hModule;
  char    szModule[MAX_MODULE_NAME32 + 1];
  char    szExePath[MAX_PATH];
} MODULEENTRY32;
typedef struct tagPROCESSENTRY32 {
  DWORD     dwSize;
  DWORD     cntUsage;
  DWORD     th32ProcessID;
  ULONG_PTR th32DefaultHeapID;
  DWORD     th32ModuleID;
  DWORD     cntThreads;
  DWORD     th32ParentProcessID;
  LONG      pcPriClassBase;
  DWORD     dwFlags;
  CHAR      szExeFile[MAX_PATH];
} PROCESSENTRY32;
HANDLE CreateToolhelp32Snapshot(DWORD dwFlags, DWORD th32ProcessID);
BOOL Module32First(HANDLE hSnapshot, void* moduleEntryPtr);
BOOL Module32Next(HANDLE hSnapshot, void* moduleEntryPtr);
BOOL Process32First(HANDLE hSnapshot, void* processEntryPtr);
BOOL Process32Next(HANDLE hSnapshot, void* processEntryPtr);
EOC), "kernel32");
