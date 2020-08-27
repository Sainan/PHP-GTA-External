<?php
require "vendor/autoload.php";
use GtaExternal\GtaExternal;
use Gui\
{Application, Components\Label};

$gta_external = (new GtaExternal());
$application = new Application([
	"title" => "PHP GTA External",
	"width" => 300,
	"height" => 120,
]);
$application->on("start", function() use ($gta_external, $application)
{
	$navigation = $gta_external->getPlayerPed()->add(0x30)->dereference();

	(new Label())->setLeft(20)->setTop(20)->setText("Detected ".$gta_external->getEditionName()." Edition");
	$label_x = (new Label())->setLeft(20)->setTop(40);
	$label_y = (new Label())->setLeft(20)->setTop(60);
	$label_z = (new Label())->setLeft(20)->setTop(80);

	$application->getLoop()->addPeriodicTimer(1 / 24, function() use ($navigation, $label_x, $label_y, $label_z)
	{
		$label_x->setText("X: ".$navigation->add(0x50)->readFloat());
		$label_y->setText("Y: ".$navigation->add(0x54)->readFloat());
		$label_z->setText("Z: ".$navigation->add(0x58)->readFloat());
	});
});
$application->run();
