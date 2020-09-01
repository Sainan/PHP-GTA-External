<?php
eval("\$crossmap = [".str_replace(["{ ", ", ", " }"], ["\"", "\" => gmp_init(\"", "\")"], file_get_contents("https://raw.githubusercontent.com/Sainan/GTA-V-Crossmap/master/crossmap.hpp"))."];");
assert(isset($crossmap));
assert(is_array($crossmap));
$natives_fh = fopen("src/Natives.php", "w");
fwrite($natives_fh, "<?php\r\nnamespace V;\r\nclass Natives\r\n{\r\n");
foreach(json_decode(file_get_contents("https://github.com/alloc8or/gta5-nativedb-data/raw/master/natives.json"), true) as $namespace => $namespace_data)
{
	//fwrite($natives_fh, "\t// ".$namespace."\r\n");
	foreach($namespace_data as $hash => $native_data)
	{
		if(array_key_exists($hash, $crossmap))
		{
			fwrite($natives_fh, "\tconst ".$native_data["name"]." = \"0x".gmp_strval($crossmap[$hash], 16)."\";\r\n");
		}
	}
}
fwrite($natives_fh, "}\r\n");
