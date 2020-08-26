#define FFI_SCOPE "cpp_api"
#define FFI_LIB "../bin/cpp_api.dll"

int32_t get_process_id(const char* exe_file);
uint64_t get_module_base(int32_t process_id, const char* module);
const char* get_module_path(int32_t process_id, const char* module);
const char* read_bytes(int32_t process_id, uint64_t address, uint8_t bytes);
void write_bytes(int32_t process_id, uint64_t address, const char* hex_data);
