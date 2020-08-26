<?php
if(is_file("PHP-GTA-External.zip"))
{
	unlink("PHP-GTA-External.zip");
}
$zip = new ZipArchive();
$zip->open("PHP-GTA-External.zip", ZipArchive::CREATE + ZipArchive::EXCL + ZipArchive::CHECKCONS) or die("Failed to create zip.\n");
$where = trim(shell_exec("where php"));
if(empty($where))
{
	die("Failed to find PHP's directory; is it not in your PATH?\n");
}
if(count(explode("\n", $where)) > 1)
{
	die("Multiple instances of PHP found.\n");
}
$php_dir = dirname($where);
foreach(["run.php", "start.bat"] as $file)
{
	$zip->addFile($file, $file);
}
$zip->addFile($php_dir."\\php.exe", "php/php.exe");
$zip->addFile($php_dir."\\ext\\php_ffi.dll", "php/ext/php_ffi.dll");
function recursively_add_dir($dir)
{
	global $zip;
	foreach(scandir($dir) as $file)
	{
		if(substr($file, 0, 1) != "." && substr($file, -4) != ".cpp")
		{
			$path = $dir."/".$file;
			if(is_dir($path))
			{
				recursively_add_dir($path);
			}
			else
			{
				$zip->addFile($path, $path);
			}
		}
	}
}
recursively_add_dir("bin");
recursively_add_dir("src");
recursively_add_dir("vendor");
$zip->close();
