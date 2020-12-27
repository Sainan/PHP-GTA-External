<?php
$crossmap = [];
foreach(explode("\n", file_get_contents("https://raw.githubusercontent.com/Sainan/GTA-V-Script-Decompiler/master/GTA%20V%20Script%20Decompiler/Resources/native_translation.txt")) as $line)
{
	$line = rtrim($line);
	$arr = explode(":", $line);
	if(count($arr) != 2)
	{
		continue;
	}
	$crossmap["0x".$arr[1]] = gmp_init("0x".$arr[0]);
}
foreach([
	"0x4EDE34FBADD967A6",
	"0xE81651AD79516E48",
	"0xB8BA7F44DF1575E1",
	"0xEB1C67C3A5333A92",
	"0xC4BB298BD441BE78",
	"0x83666F9FB8FEBD4B",
	"0xC9D9444186B5A374",
	"0xC1B1E9A034A63A62",
	"0x5AE11BC36633DE4E",
	"0x0000000050597EE2",
	"0x0BADBFA3B172435F",
	"0xD0FFB162F40A139C",
	"0x71D93B57D07F9804",
	"0xE3621CC40F31FE2E",
	"0xE816E655DE37FE20",
	"0x652D2EEEF1D3E62C",
	"0xA8CEACB4F35AE058",
	"0x2A488C176D52CCA5",
	"0xB7A628320EFF8E47",
	"0xEDD95A39E5544DE8",
	"0x97EF1E5BCE9DC075",
	"0xF34EE736CF047844",
	"0x11E019C8F43ACC8A",
	"0xF2DB717A73826179",
	"0xBBDA792448DB5A89",
	"0x42B65DEEF2EDF2A1",
] as $system_native)
{
	$crossmap[$system_native] = gmp_init($system_native);
}
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
