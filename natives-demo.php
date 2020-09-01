<?php
require "vendor/autoload.php";
use V\
{GTA, NativeInvoker, Natives};

$gta = new GTA();
$gta->initPatternScanResultsCache();
NativeInvoker::init($gta);

NativeInvoker::invoke(Natives::GET_GAME_TIMER);
echo "Game Timer: ".NativeInvoker::getReturnInt64()."\n";

sleep(1);

NativeInvoker::reset();
NativeInvoker::pushArgUint64(0);
NativeInvoker::pushArgUint64(244); // INPUT_INTERACTION_MENU

do
{
	NativeInvoker::invoke(Natives::IS_CONTROL_PRESSED);
	echo NativeInvoker::getReturnInt64()."\n";
}
while(true);
