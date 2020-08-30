<?php
use PWH\Pattern;
use PWH\Pointer;
use V\GTA;
require "vendor/autoload.php";
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

try
{
	$gta = new GTA();
}
catch(Exception $e)
{
	return;
}

echo "\nGTA is open (".$gta->getUniqueVersionAndEditionName()."), scanning for this pattern...\n";

$matches = 0;
$pattern->scanAll($gta->module, function(Pointer $pointer) use ($gta, &$matches)
{
	echo "> Match at ".$gta->module->name."+".$gta->module->getOffsetTo($pointer)." (".$pointer.")\n";
	$matches++;
});
echo $matches." match".($matches == 1 ? "" : "es")." total.\n";
