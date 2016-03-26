<?php
function write($file, $data){
	$handle = fopen($file, "a+");
	fwrite($handle, $data . PHP_EOL);
	fclose($handle);
}

function lepus_validCron($time, $url){
	if(strlen($url) > 128) return 'not_valid';
	if(preg_match('/[^0-9a-zA-Z.=_\&\-\?\:\/]/', $url)) return 'not_valid'; // only 0-9a-zA-Z.=_&-?:/
	$arr = explode(" ", $time);
	if(count($arr) != 5) return 'not_valid';
	foreach($arr as $val){
		if(strlen($val) > 4) return 'not_valid';
		if(preg_match('/[^0-9\*\/]/', $val)) return 'not_valid';
	}
	return 'valid';
}

$dir = "/etc/cron.d";
$data = json_decode(file_get_contents("https://lepus.su/public/api/cron.php?passwd=".md5('secret')), true);
if(!is_array($data)) die;

foreach($data as $key => $val){
	if(!is_numeric($key)) continue;
	if(!file_exists("$dir/$key") || $val["max"] > filemtime("$dir/$key")){
		unset($val["max"]);
		if(file_exists("$dir/$key")) unlink("$dir/$key");
		foreach($val as $str){
			if(lepus_validCron($str['time'], $str['url'] == 'valid')){
				write("$dir/$key", "{$str['time']} lepus /usr/bin/curl --silent '{$str['url']}' >/dev/null 2>&1");
			}
		}
	}
}

$files = scandir($dir);
foreach($files as $val){
	if($val == '.' || $val == '..' || $val == '.placeholder' || $val == 'php5') continue;
	if(empty($data[$val])){
		unlink("$dir/$val");
	}
}

shell_exec("/etc/init.d/cron reload");
