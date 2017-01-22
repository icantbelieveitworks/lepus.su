#!/usr/bin/php
<?php
$data = array();
$dir = '/etc/apache2/sites-enabled/';
$hosts = array_diff(scandir($dir), array('..', '.'));
function lepusGetWWW($dir, $value){
	$data = array();
	$l = explode(PHP_EOL, file_get_contents($dir.$value));
	foreach($l as $str){
		if(stripos($str, 'ServerName') !== FALSE){
			$data[$value]['name'] = trim(str_replace("ServerName", "", $str));
		}
		if(stripos($str, 'ServerAlias') !== FALSE){
			$data[$value]['alias'] =  trim(str_replace("ServerAlias", "", $str));
		}
	}
	return $data;
}

function lepusCertbot($cmd, $flag = ""){
	echo "certbot --apache $cmd -m poiuty@lepus.su --agree-tos --text --non-interactive --no-redirect $flag\n";
	return shell_exec("certbot --apache $cmd -m poiuty@lepus.su --agree-tos --text --non-interactive --no-redirect $flag");
	return 'OK';
}

foreach($hosts as $value){
	if(stripos($value, '-le-ssl.conf') !== FALSE){
		continue;
	}
	$data = array_merge($data, lepusGetWWW($dir, $value));
}

foreach($data as $key => $value){
	$str = ""; $arr = array();
	if (preg_match('/[^a-z0-9.-]/', $key) || $key == ''){
		continue;
	}
	$a = explode(" ", $value["alias"]);
	foreach($a as $s){
		if (preg_match('/[^a-z0-9.-]/', $s) || $s == ''){
			continue;
		}
		$str .= " -d $s";
	}
	$cmd = str_replace('  ', ' ', "-d {$value["name"]} $str");
	$le = $value['name'].'-le-ssl.conf';
	if (!file_exists($dir.$le)) {
		$output = lepusCertbot($cmd);
		echo $output;
	}else{
		$arr = array_merge($arr, lepusGetWWW($dir, $le));
		if($arr[$le]['alias'] != $value['alias']){
			echo "{$arr[$le]['alias']} != {$value['alias']}\n";
			$output = lepusCertbot($cmd, "--expand");
			echo $output;
		}
	}
	unset($arr);
}
