<?
$ips = explode(PHP_EOL ,file_get_contents("./ips"));

function check_ip($ip){
	$connection = fsockopen($ip, 1500, $errno, $errstr, 1);
	return $connection;
}

foreach ($ips as $value) {
	if(check_ip($value) === FALSE) echo "$value YES!<br/>";
}
