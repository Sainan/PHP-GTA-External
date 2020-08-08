#include <iostream>
#include <Windows.h>
#include <TlHelp32.h>

uint32_t get_process_id(const char* process)
{
    HANDLE processSnapshot = CreateToolhelp32Snapshot(TH32CS_SNAPPROCESS, 0);
	if (processSnapshot != INVALID_HANDLE_VALUE)
	{
		PROCESSENTRY32 processEntry;
		processEntry.dwSize = sizeof(PROCESSENTRY32);
		if (Process32First(processSnapshot, &processEntry))
		{
			do
			{
				if (strcmp(process, processEntry.szExeFile) == 0)
				{
					CloseHandle(processSnapshot);
					return processEntry.th32ProcessID;
				}
			} while (Process32Next(processSnapshot, &processEntry));
		}
		CloseHandle(processSnapshot);
	}
	std::cerr << "Failed to find process";
	exit(1);
}
