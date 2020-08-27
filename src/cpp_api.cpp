#include <iostream>
#include <string>
#include <iomanip>
#include <Windows.h>
#include <TlHelp32.h>

#define og_extern extern
#define extern og_extern "C" __declspec(dllexport)
#include "cpp_api.h"

static char return_buffer[260];
static_assert(sizeof(return_buffer) >= MAX_PATH, "return buffer can't hold a path");

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
					strncpy_s(return_buffer, moduleEntry.szExePath, sizeof(return_buffer));
					return return_buffer;
				}
			} while (Module32Next(moduleSnapshot, &moduleEntry));
		}
		CloseHandle(moduleSnapshot);
	}
	return "";
}

const char* read_bytes(int32_t process_id, uint64_t address, uint8_t bytes)
{
	constexpr uint8_t limit = (sizeof(return_buffer) / 2) - 1;
	if (bytes > limit)
	{
		std::cerr << "Warning: read_bytes limit surpassed, result will be shorter than requested!";
		bytes = limit;
	}
	const HANDLE handle = OpenProcess(PROCESS_VM_READ, FALSE, process_id);
	auto* const buffer = (char*)malloc(bytes);
	std::stringstream hex_str;
	ReadProcessMemory(handle, (void*)address, buffer, bytes, nullptr);
	CloseHandle(handle);
	for (uint8_t i = 0; i < bytes; i++)
	{
		hex_str << std::setw(2) << std::setfill('0') << std::hex << (0xFF & buffer[i]);
	}
	free(buffer);
	strncpy_s(return_buffer, hex_str.str().c_str(), sizeof(return_buffer));
	return return_buffer;
}

void write_bytes(int32_t process_id, uint64_t address, const char* hex_data)
{
	const size_t bytes = strlen(hex_data) / 2;
	auto buffer = (char*)malloc(bytes);
	for (size_t i = 0; i < bytes; i++)
	{
		buffer[i] = (char)std::strtoul(hex_data + (i * 2), (char**)&hex_data + (i * 2) + 1, 16);
	}
	const HANDLE handle = OpenProcess(PROCESS_VM_WRITE, FALSE, process_id);
	WriteProcessMemory(handle, (void*)address, buffer, bytes, nullptr);
	free(buffer);
	CloseHandle(handle);
}
