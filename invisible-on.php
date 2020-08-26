<?php
require "src/GtaExternal.php";
(new GtaExternal\GtaExternal())->getPlayerPed()->add(0x02C)->writeByte(0);
