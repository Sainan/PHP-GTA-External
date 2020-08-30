<?php
require "vendor/autoload.php";
use V\
{GTA, Kernel32, Pointer\Pointer};
use const V\
{PROCESS_CREATE_THREAD, PROCESS_VM_OPERATION, PROCESS_VM_READ, PROCESS_VM_WRITE};
$LoadLibraryA_fp = Kernel32::GetProcAddress(Kernel32::GetModuleHandleA("kernel32.dll"), "LoadLibraryA");
if ($LoadLibraryA_fp == Pointer::nullptr)
{
	die("Failed to find LoadLibraryA.\n");
}

if(empty($argv[1]))
{
	die(/** @lang text */ "Syntax: php inject.php <dll path>\n");
}

$gta = new GTA(PROCESS_CREATE_THREAD | PROCESS_VM_OPERATION | PROCESS_VM_READ | PROCESS_VM_WRITE);
$parameter = $gta->allocate(strlen($argv[1]));
$parameter->writeString($argv[1]);
Kernel32::CreateRemoteThread($gta->module->processHandle, $LoadLibraryA_fp, $parameter);
echo "Successfully injected.\n";
