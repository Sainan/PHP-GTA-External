<?php
require "php-src/GtaExternal.php";
(new GtaExternal\GtaExternal())->getPlayerPed()->add(0x02C)->writeByte(255);
