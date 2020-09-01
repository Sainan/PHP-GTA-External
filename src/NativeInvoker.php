<?php
namespace V;
use FFI;
use GMP;
use PWH\
{AllocPointer, Module, Pattern};
class NativeInvoker
{
	private const OFFSET_ARG_COUNT = 8;

	private static Module $gtaModule;
	private static int $natives_registration_address;
	private static int $get_native_handler_address;
	private static AllocPointer $return_stack;
	private static AllocPointer $arg_stack;
	private static AllocPointer $native_call_context;
	private static array $entrypoints = [];

	/** @noinspection PhpUndefinedFieldInspection */
	static function init(GTA $gta) : void
	{
		$res = $gta->getPatternScanResult("Natives", function() : Pattern
		{
			return Pattern::ida("48 8D 0D ? ? ? ? 48 8B 14 FA E8 ? ? ? ? 48 85 C0 75 0A");
		});
		self::$gtaModule = $gta->module;
		self::$natives_registration_address = $res->add(3)->rip()->address;
		self::$get_native_handler_address = $res->add(12)->rip()->address;
		self::$return_stack = $gta->module->allocate(8 * 10);
		self::$arg_stack = $gta->module->allocate(8 * 100);
		$local_native_call_context = FFI::new(FFI::type(<<<EOC
struct
{
	uint64_t return_value; // 0
	uint32_t arg_count; // 8
	uint64_t args;
	int32_t data_count;
	uint32_t data[48];
}
EOC));
		$local_native_call_context->return_value = self::$return_stack->address;
		$local_native_call_context->args = self::$arg_stack->address;
		self::$native_call_context = $gta->module->allocate(FFI::sizeof($local_native_call_context));
		self::$native_call_context->writeString(FFI::string($local_native_call_context, FFI::sizeof($local_native_call_context)));
	}

	static function reset() : void
	{
		self::$native_call_context->add(self::OFFSET_ARG_COUNT)->writeUInt32(0);
	}

	/**
	 * @param int|string|GMP $value
	 */
	static function pushArgUint64($value): void
	{
		$i = self::$native_call_context->add(8)->readUInt32();
		self::$arg_stack->add(8 * ($i++))->writeUInt64($value);
		self::$native_call_context->add(8)->writeUInt32($i);
	}

	static function invoke($hash) : void
	{
		if(!array_key_exists($hash, self::$entrypoints))
		{
			self::$entrypoints[$hash] = self::$gtaModule->callUInt64Function(self::$get_native_handler_address, self::$natives_registration_address, $hash);
		}
		self::$gtaModule->callVoidFunction(self::$entrypoints[$hash], self::$native_call_context->address);
	}

	static function getReturnInt64() : int
	{
		return self::$return_stack->readInt64();
	}

	static function getReturnUint64() : GMP
	{
		return self::$return_stack->readUInt64();
	}
}
