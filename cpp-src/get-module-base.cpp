#include "common.hpp"

int main(int argc, char* argv[])
{
	if (argc < 3)
	{
		std::cerr << "Usage: get-module-base.exe <process> <module>\n";
		return 1;
	}
	HANDLE moduleSnapshot = CreateToolhelp32Snapshot(TH32CS_SNAPMODULE | TH32CS_SNAPMODULE32, get_process_id(argv[1]));
	if (moduleSnapshot != INVALID_HANDLE_VALUE)
	{
		MODULEENTRY32 moduleEntry;
		moduleEntry.dwSize = sizeof(MODULEENTRY32);
		if (Module32First(moduleSnapshot, &moduleEntry))
		{
			do
			{
				if (strcmp(argv[2], moduleEntry.szModule) == 0)
				{
					CloseHandle(moduleSnapshot);
					std::cout << std::to_string((uint64_t)moduleEntry.modBaseAddr);
					return 0;
				}
			} while (Module32Next(moduleSnapshot, &moduleEntry));
		}
		CloseHandle(moduleSnapshot);
	}
	std::cerr << "Failed to find module";
	return 1;
}
