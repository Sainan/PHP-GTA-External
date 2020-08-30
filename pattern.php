<?php
require "vendor/autoload.php";
use V\
{GTA, Pattern, Pointer\Pointer};
use const V\GTA_MODULE;
if(empty($argv[1]))
{
	die(/** @lang text */ "Syntax: php pattern.php <pattern> [mask]\n");
}

if(!empty($argv[3]))
{
	die("Too many arguments! Did you forget to put one of your arguments in \"quotes\"?\n");
}

if(empty($argv[2]))
{
	if(Pattern::isIdaPatternString($argv[1]))
	{
		echo "Looks like an IDA-style pattern.\n\n";
		$pattern = Pattern::ida($argv[1]);
	}
	else
	{
		echo "Doesn't look like an IDA-style pattern, assuming binary pattern.\n\n";
		$pattern = Pattern::escapedBinary($argv[1]);
	}
}
else
{
	echo "Mask is given, assuming binary pattern.\n\n";
	$pattern = Pattern::escapedBinary($argv[1], $argv[2]);
}

echo "new Pattern(".$pattern->toPatternString().")\n";
echo "Pattern::ida(\"".$pattern->toIdaPatternString()."\")\n";
echo "Pattern::binary(".$pattern->toBinaryPatternString().")\n\n";

$gta = GTA::tryConstruct();
if(!$gta instanceof GTA)
{
	return;
}

echo "\nGTA is open (".$gta->getOnlineVersion().", ".$gta->getEditionName()." Edition), scanning for this pattern...\n";

$module = $gta->getModule();
$matches = 0;
$pattern->scanAll($module, function(Pointer $pointer) use ($module, &$matches)
{
	echo "> Match at ".$module->name."+".$module->getOffsetTo($pointer)." (".$pointer.")\n";
	$matches++;
});
echo $matches." match".($matches == 1 ? "" : "es")." total.\n";
