<?php
require "vendor/autoload.php";
use PWH\Pattern;
use V\GTA;

$gta = new GTA();
$gta->initPatternScanResultsCache();
$res = $gta->getPatternScanResult("Natives", function() : Pattern
{
	return Pattern::ida("48 8D 0D ? ? ? ? 48 8B 14 FA E8 ? ? ? ? 48 85 C0 75 0A");
});
$IS_CONTROL_PRESSED = $gta->module->callPtrFunction(
	$res->add(12)->rip()->address,
	$res->add(3)->rip()->address,
	0x06F8112AA79C53D9 // from crossmap
);

$return_stack = $gta->module->allocate(8 * 10);
$arg_stack = $gta->module->allocate(8 * 100);

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
$local_native_call_context->return_value = $return_stack->address;
$local_native_call_context->args = $arg_stack->address;

$native_call_context = $gta->module->allocate(FFI::sizeof($local_native_call_context));
$native_call_context->writeString(FFI::string($local_native_call_context, FFI::sizeof($local_native_call_context)));

function push_arg(int $value) : void
{
	global $native_call_context, $arg_stack;
	$i = $native_call_context->add(8)->readUInt32();
	$arg_stack->add(8 * ($i++))->writeUInt64($value);
	$native_call_context->add(8)->writeUInt32($i);
}

push_arg(0);
push_arg(244); // INPUT_INTERACTION_MENU

do
{
	$gta->module->callVoidFunction($IS_CONTROL_PRESSED->address, $native_call_context->address);
	echo "INPUT_INTERACTION_MENU is ".($return_stack->readUInt64() == 0  ? "not " : "")."pressed\n";
}
while(true);
