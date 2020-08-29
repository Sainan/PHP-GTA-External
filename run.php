<?php
require "vendor/autoload.php";
use V\GTA;
use Gui\
{Application, Components\Button, Components\InputNumber, Components\Label, Components\Window};
$gta = (new GTA());
$application = new Application([
	"title" => "PHP V",
	"width" => 300,
	"height" => 90,
	"icon" => __DIR__."\\purple-v.ico",
]);
$application->on("start", function() use ($gta, $application)
{
	(new Label())->setLeft(20)->setTop(20)->setText("Detected ".$gta->getEditionName()." Edition, Online ".$gta->getOnlineVersion());

	(new Button())->setLeft(20)->setTop(40)->setValue("Self")->on("click", function() use ($gta, $application)
	{
		$window = new Window([
			"title" => "PHP V: Self",
			"top" => $application->getWindow()->getTop() + $application->getWindow()->getHeight() + 30,
			"left" => $application->getWindow()->getLeft(),
			"width" => 300,
			"height" => 110,
			"icon" => __DIR__."\\purple-v.ico",
		]);

		$label_x = (new Label([], $window))->setLeft(20)->setTop(20)->setText("Looking for Ped Factory...");
		$label_y = (new Label([], $window))->setLeft(20)->setTop(40);
		$label_z = (new Label([], $window))->setLeft(20)->setTop(60);

		$label_health = (new Label([], $window))->setLeft(160)->setTop(20);
		$label_armor = (new Label([], $window))->setLeft(160)->setTop(40);
		(new Button([], $window))->setLeft(160)->setTop(60)->setValue("Refill")->on("click", function() use ($gta)
		{
			$gta->getPlayerPed()->setHealth(200.0)
				->setArmor(100.0);
		});

		$timer = $application->getLoop()->addPeriodicTimer(1 / 24, function() use ($gta, $label_x, $label_y, $label_z, $label_health, $label_armor)
		{
			$ped = $gta->getPlayerPed();

			$pos = $ped->getNavigation()->getPosition();
			$pos->bufferXYZ();
			$label_x->setText("X: ".$pos->readX());
			$label_y->setText("Y: ".$pos->readY());
			$label_z->setText("Z: ".$pos->readZ());

			$label_health->setText("Health: ".$ped->getHealth());
			$label_armor->setText("Armor: ".$ped->getArmor());
		});
		$window->on("close", function() use ($timer)
		{
			$timer->cancel();
		});
	});

	(new Button())->setLeft(100)->setTop(40)->setValue("Globals")->on("click", function() use ($gta, $application)
	{
		$window = new Window([
			"title" => "PHP V: Globals",
			"top" => $application->getWindow()->getTop(),
			"left" => $application->getWindow()->getLeft() + $application->getWindow()->getWidth(),
			"width" => 250,
			"height" => 90,
			"icon" => __DIR__."\\purple-v.ico"
		]);

		$mode = 0;
		$button_mode = (new Button([], $window))->setLeft(20)->setTop(20)->setWidth(100)->setValue("Read");
		$button_mode->on("click", function() use ($button_mode, &$mode)
		{
			if(++$mode > 2)
			{
				$mode = 0;
			}
			switch($mode)
			{
				case 0:
					$button_mode->setValue("Read");
					break;

				case 1:
					$button_mode->setValue("Write Int32");
					break;

				case 2:
					$button_mode->setValue("Write Float");
					break;
			}
		});

		$global = 0;
		$global_has_changed = 0;
		$input_global = (new InputNumber(false, [], $window))->setLeft(130)->setTop(20)->setWidth(100)->setMin(0)->setMax(pow(2, 31) - 1);
		$input_global->on("change", function() use ($input_global, &$mode, &$global, &$global_has_changed)
		{
			$global_has_changed++;
		});

		$input_int32 = (new InputNumber(false, [], $window))->setLeft(20)->setTop(50)->setWidth(100)->setMin(pow(2, 31) * -1)->setMax(pow(2, 31) - 1);
		$input_float = (new InputNumber(true, [], $window))->setLeft(130)->setTop(50)->setWidth(100)->setMin(0.0)->setMax(PHP_FLOAT_MAX);

		$timer = $application->getLoop()->addPeriodicTimer(1 / 24, function() use ($gta, &$mode, &$global, &$global_has_changed, $input_global, &$write_as_int, $input_int32, $input_float)
		{
			if($global_has_changed > 1)
			{
				$global_has_changed = 1;
				return;
			}
			switch($mode)
			{
				case 0:
					if($global_has_changed > 0)
					{
						$global = $input_global->getValue() ?? 0;
						$global_has_changed = 0;
					}
					$script_global = $gta->getScriptGlobal($global);
					$input_int32->setValue($script_global->readInt32());
					$input_float->setValue($script_global->readFloat());
					break;

				case 1:
					if($global_has_changed > 0)
					{
						$input_global->setValue($global);
						$global_has_changed = 0;
					}
					$script_global = $gta->getScriptGlobal($global);
					$script_global->writeInt32($input_int32->getValue());
					$input_float->setValue($script_global->readFloat());
					break;

				case 2:
					if($global_has_changed > 0)
					{
						$input_global->setValue($global);
						$global_has_changed = 0;
					}
					$script_global = $gta->getScriptGlobal($global);
					$script_global->writeFloat($input_float->getValue());
					$input_int32->setValue($script_global->readInt32());
					break;
			}
		});
		$window->on("close", function() use ($timer)
		{
			$timer->cancel();
		});
	});
});
$application->setVerboseLevel(1);
register_shutdown_function(function() use ($application)
{
	$application->terminate();
});
sapi_windows_set_ctrl_handler(function() use ($application)
{
	$application->terminate();
});
$application->run();
