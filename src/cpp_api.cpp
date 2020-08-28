#include <iostream>
#include <string>
#include <iomanip>
#include <Windows.h>
#include <TlHelp32.h>

#define og_extern extern
#define extern og_extern "C" __declspec(dllexport)
#include "cpp_api.h"

static char buffer[0xffff];

int32_t get_process_id(const char* exe_file)
{
	const HANDLE processSnapshot = CreateToolhelp32Snapshot(TH32CS_SNAPPROCESS, 0);
	if (processSnapshot != INVALID_HANDLE_VALUE)
	{
		PROCESSENTRY32 processEntry;
		processEntry.dwSize = sizeof(PROCESSENTRY32);
		if (Process32First(processSnapshot, &processEntry))
		{
			do
			{
				if (strcmp(exe_file, processEntry.szExeFile) == 0)
				{
					CloseHandle(processSnapshot);
					return processEntry.th32ProcessID;
				}
			} while (Process32Next(processSnapshot, &processEntry));
		}
		CloseHandle(processSnapshot);
	}
	return -1;
}

uint64_t get_module_base(int32_t process_id, const char* module)
{
	const HANDLE moduleSnapshot = CreateToolhelp32Snapshot(TH32CS_SNAPMODULE | TH32CS_SNAPMODULE32, process_id);
	if (moduleSnapshot != INVALID_HANDLE_VALUE)
	{
		MODULEENTRY32 moduleEntry;
		moduleEntry.dwSize = sizeof(MODULEENTRY32);
		if (Module32First(moduleSnapshot, &moduleEntry))
		{
			do
			{
				if (strcmp(module, moduleEntry.szModule) == 0)
				{
					CloseHandle(moduleSnapshot);
					return (uint64_t)moduleEntry.modBaseAddr;
				}
			} while (Module32Next(moduleSnapshot, &moduleEntry));
		}
		CloseHandle(moduleSnapshot);
	}
	return 0;
}

uint64_t get_module_size(int32_t process_id, const char* module)
{
	const HANDLE moduleSnapshot = CreateToolhelp32Snapshot(TH32CS_SNAPMODULE | TH32CS_SNAPMODULE32, process_id);
	if (moduleSnapshot != INVALID_HANDLE_VALUE)
	{
		MODULEENTRY32 moduleEntry;
		moduleEntry.dwSize = sizeof(MODULEENTRY32);
		if (Module32First(moduleSnapshot, &moduleEntry))
		{
			do
			{
				if (strcmp(module, moduleEntry.szModule) == 0)
				{
					CloseHandle(moduleSnapshot);
					return moduleEntry.modBaseSize;
				}
			} while (Module32Next(moduleSnapshot, &moduleEntry));
		}
		CloseHandle(moduleSnapshot);
	}
	return 0;
}

const char* get_module_path(int32_t process_id, const char* module)
{
	const HANDLE moduleSnapshot = CreateToolhelp32Snapshot(TH32CS_SNAPMODULE | TH32CS_SNAPMODULE32, process_id);
	if (moduleSnapshot != INVALID_HANDLE_VALUE)
	{
		static MODULEENTRY32 moduleEntry;
		moduleEntry.dwSize = sizeof(MODULEENTRY32);
		if (Module32First(moduleSnapshot, &moduleEntry))
		{
			do
			{
				if (strcmp(module, moduleEntry.szModule) == 0)
				{
					CloseHandle(moduleSnapshot);
					strncpy_s(buffer, moduleEntry.szExePath, MAX_PATH);
					return buffer;
				}
			} while (Module32Next(moduleSnapshot, &moduleEntry));
		}
		CloseHandle(moduleSnapshot);
	}
	return "";
}


uint64_t open_process(int32_t process_id)
{
	return (uint64_t)OpenProcess(PROCESS_VM_OPERATION | PROCESS_VM_READ | PROCESS_VM_WRITE, FALSE, process_id);
}

void close_handle(uint64_t handle)
{
	CloseHandle((HANDLE)handle);
}


uint16_t buffer_size()
{
	return sizeof(buffer);
}

uint8_t buffer_read_byte(uint16_t index)
{
	return (uint8_t)buffer[index];
}

void buffer_write_byte(uint16_t index, uint8_t value)
{
	buffer[index] = (char)value;
}

uint8_t process_read_byte(uint64_t handle, uint64_t address)
{
	ReadProcessMemory((HANDLE)handle, (void*)address, buffer, 1, nullptr);
	return (uint8_t)buffer[0];
}

void process_read_bytes(uint64_t handle, uint64_t address, uint16_t bytes)
{
	ReadProcessMemory((HANDLE)handle, (void*)address, buffer, bytes, nullptr);
}

void process_write_bytes(uint64_t handle, uint64_t address, uint16_t bytes)
{
	WriteProcessMemory((HANDLE)handle, (void*)address, buffer, bytes, nullptr);
}
