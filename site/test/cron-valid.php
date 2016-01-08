<pre><?php

function lepus_validCron($time, $url, $i = 0){
	if(preg_match('/[^0-9a-zA-Z.=_\&\-\?\:\/]/', $url)) return 'not_valid'; // only 0-9a-zA-Z.=_&-?:/
	$arr = explode(" ", $time);
	if(count($arr) != 5) return 'wrong_cron';
	foreach($arr as $val){
		$i++; if($i == 5) $len = 1; else $len = 2;
		if(strlen($val) > $len) return 'not_valid';
		if(preg_match('/[^0-9\*]/', $val)) return 'not_valid';
	}
	return 'valid';
}

$i = '* * * * *';
$url = 'http://lolka.super/';
echo lepus_validCron($i, $url);
