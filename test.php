<?php
require "vendor/autoload.php";
use V\GTA;

$gta = new GTA();

echo $gta->module->base->add(38588416)->rip()->readUInt64()."\n";
