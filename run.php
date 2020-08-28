<?php
require "vendor/autoload.php";
use GtaExternal\GtaExternal;
use Gui\
{Application, Components\Button, Components\Label};

$gta_external = (new GtaExternal());
$gta_external->ensurePedFactoryPtr();

$application = new Application([
	"title" => "PHP GTA External",
	"width" => 300,
	"height" => 120,
]);
$application->on("start", function() use ($gta_external, $application)
{
	(new Label())->setLeft(20)->setTop(20)->setText("Detected ".$gta_external->getEditionName()." Edition");

	$label_x = (new Label())->setLeft(20)->setTop(40);
	$label_y = (new Label())->setLeft(20)->setTop(60);
	$label_z = (new Label())->setLeft(20)->setTop(80);

	$label_health = (new Label())->setLeft(160)->setTop(40);
	$label_armor = (new Label())->setLeft(160)->setTop(60);
	(new Button())->setLeft(160)->setTop(80)->setValue("Refill")->on("click", function() use ($gta_external)
	{
		$gta_external->getPlayerPed()->setHealth(200.0)
									 ->setArmor(100.0);
	});

	$application->getLoop()->addPeriodicTimer(1 / 24, function() use ($gta_external, $label_x, $label_y, $label_z, $label_health, $label_armor)
	{
		$ped = $gta_external->getPlayerPed();

		$pos = $ped->getNavigation()->getPosition();
		$pos->bufferXYZ();
		$label_x->setText("X: ".$pos->readX());
		$label_y->setText("Y: ".$pos->readY());
		$label_z->setText("Z: ".$pos->readZ());

		$label_health->setText("Health: ".$ped->getHealth());
		$label_armor->setText("Armor: ".$ped->getArmor());
	});
});
$application->setVerboseLevel(1);
sapi_windows_set_ctrl_handler(function() use ($application)
{
	$application->terminate();
});
$application->run();
