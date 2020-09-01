<?php /** @noinspection PhpComposerExtensionStubsInspection */
echo "Packaging...\n";
if(is_file("PHP-V.zip"))
{
	unlink("PHP-V.zip");
}
$zip = new ZipArchive();
$zip->open("PHP-V.zip", ZipArchive::CREATE + ZipArchive::EXCL + ZipArchive::CHECKCONS) or die("Failed to create zip.\n");
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
foreach(["purple-v.ico", "php-v.php", "php-v.bat", "dll-injector.php", "dll-injector.bat"] as $file)
{
	$zip->addFile($file, "PHP-V/".$file);
}
$zip->addFile($php_dir."\\php.exe", "PHP-V/php/php.exe");
$zip->addFile($php_dir."\\php".PHP_MAJOR_VERSION.".dll", "PHP-V/php/php".PHP_MAJOR_VERSION.".dll");
$zip->addFile($php_dir."\\ext\\php_ffi.dll", "PHP-V/php/ext/php_ffi.dll");
function recursively_add_dir($dir)
{
	global $zip;
	foreach(scandir($dir) as $file)
	{
		if(!in_array($file, [".", "..", ".git", "examples", "x86_64-linux", "phpgui-i386-darwin", "phpgui-i386-win32.exe", "phpgui-x86_64-freebsd", "phpgui-x86_64-linux"]))
		{
			$path = $dir."/".$file;
			if(is_dir($path))
			{
				recursively_add_dir($path);
			}
			else
			{
				$zip->addFile($path, "PHP-V/".$path);
			}
		}
	}
}
recursively_add_dir("src");
recursively_add_dir("vendor");
$zip->close();
