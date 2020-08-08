#include "common.hpp"

#include <string>
#include <iomanip>

int main(int argc, char* argv[])
{
	if (argc < 4)
	{
		std::cerr << "Usage: read-bytes.exe <process> <address> <bytes>\n";
		return 1;
	}
	const size_t size = std::stoull(argv[3]);
	auto buffer = (char*)malloc(size);
	HANDLE handle = OpenProcess(PROCESS_VM_READ, FALSE, get_process_id(argv[1]));
	ReadProcessMemory(handle, (void*)std::stoull(argv[2]), buffer, size, nullptr);
	for (size_t i = 0; i < size; i++)
	{
		std::cout << std::setw(2) << std::setfill('0') << std::hex << (0xFF & buffer[i]);
	}
	free(buffer);
	CloseHandle(handle);
	return 0;
}
