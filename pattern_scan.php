<?php
require "vendor/autoload.php";
use GtaExternal\
{GtaExternal, Pattern, Pointer};
use const GtaExternal\GTA_MODULE;

$gta_external = (new GtaExternal());
echo "Detected ".$gta_external->getEditionName()." Edition\n\nNote: This pattern scanning is extremely slow.\n\n";

$module = $gta_external->getModule();

echo "Looking for PedFactory... ";
$res = (new Pattern("48 8B 05 ? ? ? ? 48 8B 48 08 48 85 C9 74 52 8B 81"))->scan($module);
if($res instanceof Pointer)
{
	$addr = $res->add(3)->rip()->address;
	echo " Found at ".GTA_MODULE."+".dechex($addr - $module->base->address)." (".dechex($addr).")\n";
}
else
{
	echo "Pattern not found. :(\n";
}
