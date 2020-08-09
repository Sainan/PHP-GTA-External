<?php
require "php-src/GtaExternal.php";

$navigation = (new \GtaExternal\GtaExternal())->getPlayerPed()->add(0x30)->dereference();
echo "X: ".$navigation->add(0x50)->readFloat()."\n";
echo "Y: ".$navigation->add(0x54)->readFloat()."\n";
echo "Z: ".$navigation->add(0x58)->readFloat()."\n";
