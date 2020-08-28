<?php
require "vendor/autoload.php";
use GtaExternal\
{GtaExternal, Pattern, Pointer\Pointer};
use const GtaExternal\GTA_MODULE;

$gta_external = (new GtaExternal());
echo "Detected ".$gta_external->getEditionName()." Edition\n\nNote: This pattern scanning is relatively slow.\n\n";

$module = $gta_external->getModule();

echo "Looking for PedFactory... ";
$res = (new Pattern("48 8B 05 ? ? ? ? 48 8B 48 08 48 85 C9 74 52 8B 81"))->scan($module);
if($res instanceof Pointer)
{
	$res = $res->add(3)->rip();
	echo " Found at ".GTA_MODULE."+".dechex($module->getOffsetTo($res))." (".$res.")\n";
}
else
{
	echo "Pattern not found. :(\n";
}
