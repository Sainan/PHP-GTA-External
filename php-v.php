<?php
require "vendor/autoload.php";
use Gui\Application;
use Gui\Components\
{Button, InputNumber, Label, Window};
use V\GTA;
$gta = new GTA();
$gta->initPatternScanResultsCache();
$application = new Application([
	"title" => "PHP V",
	"width" => 300,
	"height" => 90,
	"icon" => __DIR__."\\purple-v.ico",
]);
$application->on("start", function() use ($gta, $application)
{
	$application->getWindow()->on("resize", function() use ($application)
	{
		$application->getWindow()->setWidth(300)->setHeight(90);
	});

	(new Label())->setLeft(20)->setTop(20)->setText("Detected ".$gta->getUniqueVersionAndEditionName());

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
		$window->on("resize", function() use ($window)
		{
			$window->setWidth(300)->setHeight(110);
		});

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
		$window->on("resize", function() use ($window)
		{
			$window->setWidth(250)->setHeight(90);
		});

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
		$input_float = (new InputNumber(true, [], $window))->setLeft(130)->setTop(50)->setWidth(100)->setMin(-PHP_FLOAT_MAX)->setMax(PHP_FLOAT_MAX);

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
					$float = $script_global->readFloat();
					$input_float->setValue(is_nan($float) ? 0.0 : $float);
					break;

				case 1:
					if($global_has_changed > 0)
					{
						$input_global->setValue($global);
						$global_has_changed = 0;
					}
					$script_global = $gta->getScriptGlobal($global);
					$script_global->writeInt32($input_int32->getValue());
					$float = $script_global->readFloat();
					$input_float->setValue(is_nan($float) ? 0.0 : $float);
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
