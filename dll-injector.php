<?php
require "vendor/autoload.php";
use Gui\Application;
use Gui\Components\
{Button, InputFile, Option, Select};
use PWH\
{Kernel32, Pointer, Process};
$LoadLibraryA_fp = Kernel32::GetProcAddress(Kernel32::GetModuleHandleA("kernel32.dll"), "LoadLibraryA");
if ($LoadLibraryA_fp == Pointer::nullptr)
{
	die("Failed to find LoadLibraryA.\n");
}
$application = new Application([
	"title" => "DLL Injector",
	"width" => 540,
	"height" => 95,
	"icon" => __DIR__."\\purple-v.ico",
]);
function getProcessOptions() : array
{
	$options = [];
	foreach(Process::getProcessList() as $process)
	{
		array_push($options, (new Option($process["exe_file"], $process["process_id"])));
	}
	return $options;
}
$application->on("start", function() use ($application, $LoadLibraryA_fp)
{
	$application->getWindow()->on("resize", function() use ($application)
	{
		$application->getWindow()->setWidth(540)->setHeight(95);
	});

	$process_select = (new Select())->setLeft(20)->setTop(50)->setWidth(300);
	$process_options = getProcessOptions();
	$process_select->setOptions($process_options);
	(new Button())->setLeft(325)->setTop(50)->setWidth(95)->setValue("Refresh")->on("click", function() use ($process_select)
	{
		$process_options = getProcessOptions();
		$process_select->setOptions($process_options);
	});
	$input_file = (new InputFile())->setLeft(20)->setTop(20)->setWidth(500)->setExtensionFilter(["dll;*.asi" => "DLL files"]);
	(new Button())->setLeft(420)->setTop(50)->setWidth(100)->setValue("Inject")->on("click", function() use ($process_select, &$process_options, $input_file, $application, $LoadLibraryA_fp)
	{
		$process = new Process($process_options[$process_select->getChecked()]->getValue());
		$i = 0;
		foreach($input_file->getValue() as $file)
		{
			$parameter = $process->module->allocate(strlen($file));
			$parameter->writeString($file);
			Kernel32::WaitForSingleObject(Kernel32::CreateRemoteThread($process->module->processHandle, $LoadLibraryA_fp, $parameter));
			$i++;
		}
		$application->alert("Injected $i DLL".($i == 1 ? "" : "s"));
	});
});
$application->setVerboseLevel(1);
register_shutdown_function(function() use ($application)
{
	$application->terminate();
});
sapi_windows_set_ctrl_handler(function() use ($application)
{
	$application->terminate();
});
$application->run();
