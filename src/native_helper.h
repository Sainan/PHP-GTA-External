#define FFI_LIB "../bin/native_helper.dll"

extern int32_t get_process_id(const char* exe_file);
extern uint64_t get_module_base(int32_t process_id, const char* module);
extern uint64_t get_module_size(int32_t process_id, const char* module);
extern const char* get_module_path(int32_t process_id, const char* module);

extern uint16_t buffer_size();
extern uint8_t buffer_read_byte(uint16_t index);
extern void buffer_write_byte(uint16_t index, uint8_t value);

extern void process_read_bytes(uint64_t handle, uint64_t address, uint16_t bytes);
extern void process_write_bytes(uint64_t handle, uint64_t address, uint16_t bytes);
