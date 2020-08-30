<?php /** @noinspection PhpUndefinedMethodInspection */
namespace V;
use FFI;
use FFI\CData;
use V\
{Handle\Handle, Handle\ObjectHandle, Handle\ProcessHandle, Pointer\Pointer};

const MAX_PATH = 260;

const PROCESS_CREATE_THREAD = 0x0002;
const PROCESS_VM_OPERATION = 0x0008;
const PROCESS_VM_READ = 0x0010;
const PROCESS_VM_WRITE = 0x0020;

const MEM_COMMIT = 0x00001000;
const MEM_RESERVE = 0x00002000;
const PAGE_EXECUTE_READWRITE = 0x40;

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
			throw new Kernel32Exception("Failed to close handle");
		}
	}

	static function GetModuleHandleA(string $module) : Handle
	{
		return new Handle(self::$ffi->GetModuleHandleA($module));
	}

	static function GetProcAddress(Handle $handle, string $func_name) : int
	{
		return self::$ffi->GetProcAddress($handle->handle, $func_name);
	}

	static function ReadProcessMemory(ProcessHandle $processHandle, int $base_address, CData $buffer, int $bytes) : void
	{
		self::$ffi->ReadProcessMemory($processHandle->handle, $base_address, FFI::addr($buffer), $bytes, Pointer::nullptr);
	}

	static function WriteProcessMemory(ProcessHandle $processHandle, int $base_address, CData $buffer, int $bytes) : void
	{
		self::$ffi->WriteProcessMemory($processHandle->handle, $base_address, FFI::addr($buffer), $bytes, Pointer::nullptr);
	}

	static function VirtualAllocEx(ProcessHandle $processHandle, int $bytes, int $allocation_type = MEM_COMMIT | MEM_RESERVE, int $protect = PAGE_EXECUTE_READWRITE) : Pointer
	{
		$pointer = new Pointer($processHandle, self::$ffi->VirtualAllocEx($processHandle->handle, Pointer::nullptr, $bytes, $allocation_type, $protect));
		if($pointer->isNullptr())
		{
			throw new Kernel32Exception("VirtualAllocEx failed to allocate {$bytes} bytes");
		}
		return $pointer;
	}

	static function OpenProcess(int $process_id, int $desired_access) : ProcessHandle
	{
		$handle = new ProcessHandle($process_id, self::$ffi->OpenProcess($desired_access, false, $process_id), $desired_access);
		if(!$handle->isValid())
		{
			throw new Kernel32Exception("Failed to open process");
		}
		return $handle;
	}

	static function CreateRemoteThread(ProcessHandle $processHandle, int $function_address, Pointer $parameter) : int
	{
		$thread_handle = self::$ffi->CreateRemoteThread($processHandle->handle, 0, 0, $function_address, $parameter->address, 0, 0);
		if($thread_handle == 0)
		{
			throw new Kernel32Exception("Failed to create remote thread");
		}
		return $thread_handle;
	}

	static function CreateToolhelp32Snapshot(int $flags, int $process_id) : ObjectHandle
	{
		return new ObjectHandle(self::$ffi->CreateToolhelp32Snapshot($flags, $process_id));
	}

	static function Module32First(ObjectHandle $snapshot_handle, CData $process_entry) : bool
	{
		return Kernel32::$ffi->Module32First($snapshot_handle->handle, FFI::addr($process_entry));
	}

	static function Module32Next(ObjectHandle $snapshot_handle, CData $process_entry) : bool
	{
		return Kernel32::$ffi->Module32Next($snapshot_handle->handle, FFI::addr($process_entry));
	}

	static function Process32First(ObjectHandle $snapshot_handle, CData $process_entry) : bool
	{
		return Kernel32::$ffi->Process32First($snapshot_handle->handle, FFI::addr($process_entry));
	}

	static function Process32Next(ObjectHandle $snapshot_handle, CData $process_entry) : bool
	{
		return Kernel32::$ffi->Process32Next($snapshot_handle->handle, FFI::addr($process_entry));
	}
}

Kernel32::$ffi = FFI::cdef(str_replace(
["CHAR", "BOOL", "DWORD",    "HMODULE", "HANDLE",   "SIZE_T",    "ULONG_PTR", "BYTE*",    "FARPROC", "LONG",     "LPCSTR",      "MAX_PATH", "MAX_MODULE_NAME32"],
["char", "bool", "uint32_t", "HANDLE",  "uint64_t", "ULONG_PTR", "uint64_t",  "uint64_t", "uint64_t", "uint32_t", "const char*",  MAX_PATH ,  MAX_MODULE_NAME32 ],
<<<EOC
// Errhandlingapi.h
DWORD GetLastError();

// Handleapi.h
BOOL CloseHandle(HANDLE hObject);

// Libloaderapi.h
HMODULE GetModuleHandleA(LPCSTR lpModuleName);
FARPROC GetProcAddress(HMODULE hModule, LPCSTR lpProcName);

// Memoryapi.h
BOOL ReadProcessMemory(HANDLE hProcess, uint64_t lpBaseAddress, void* lpBuffer, SIZE_T nSize, uint64_t lpNumberOfBytesRead);
BOOL WriteProcessMemory(HANDLE hProcess, uint64_t lpBaseAddress, void* lpBuffer, SIZE_T nSize, uint64_t lpNumberOfBytesWritten);
uint64_t VirtualAllocEx(HANDLE hProcess, uint64_t lpAddress, SIZE_T dwSize, DWORD flAllocationType, DWORD flProtect);

// Processthreadsapi.h
HANDLE OpenProcess(DWORD dwDesiredAccess, BOOL bInheritHandle, DWORD dwProcessId);
HANDLE CreateRemoteThread(HANDLE hProcess, uint64_t lpThreadAttributes, SIZE_T dwStackSize, uint64_t lpStartAddress, uint64_t lpParameter, DWORD dwCreationFlags, uint64_t lpThreadId);

// tlhelp32.h
typedef struct tagMODULEENTRY32 {
  DWORD   dwSize;
  DWORD   th32ModuleID;
  DWORD   th32ProcessID;
  DWORD   GlblcntUsage;
  DWORD   ProccntUsage;
  BYTE*   modBaseAddr;
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
