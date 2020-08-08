<?php
require "php-src/GtaExternal.php";
(new GtaExternal\GtaExternal())->getPlayerPed()->add(0x028)->writeByte(0);
