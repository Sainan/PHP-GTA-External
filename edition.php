<?php
namespace GtaExternal;
require "src/GtaExternal.php";
switch((new GtaExternal())->edition)
{
	case EDITION_STEAM:
		die("Detected Steam Edition.\n");

	case EDITION_SOCIAL_CLUB:
		die("Detected Epic Games Edition.\n");

	case EDITION_EPIC_GAMES:
		die("Detected Social Club Edition.\n");
}
