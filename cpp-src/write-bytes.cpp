#include "common.hpp"

#include <string>
#include <iomanip>

int main(int argc, char* argv[])
{
	if (argc < 4)
	{
		std::cerr << "Usage: write-bytes.exe <process> <address> <hex-encoded data>\n";
		return 1;
	}
	const size_t size = strlen(argv[3]) / 2;
	auto buffer = (char*)malloc(size);
	for (size_t i = 0; i < size; i++)
	{
		buffer[i] = (char)std::strtoul(std::string(argv[3] + (i * 2), 2).c_str(), nullptr, 16);
	}
	HANDLE handle = OpenProcess(PROCESS_VM_WRITE, FALSE, get_process_id(argv[1]));
	WriteProcessMemory(handle, (void*)std::stoull(argv[2]), buffer, size, nullptr);
	free(buffer);
	CloseHandle(handle);
	return 0;
}
