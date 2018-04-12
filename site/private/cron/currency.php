<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

$i = file_get_contents('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml');
$xml = simplexml_load_string($i);
$count = count($xml->Cube->Cube);
for ($i=0;$i<$count;$i++) {
	foreach($xml->Cube->Cube[$i]->Cube as $rate) {
		if($rate['currency'] == 'RUB'){
			$j += floatval($rate['rate']);
		}
	}
}
$j = round($j/$count, 2);
if(is_numeric($j) && $j > 40 && $j < 160){
	$update = $db->prepare("UPDATE `currency` SET `val` =:rate WHERE `name` = 'EUR'");
	$update->bindParam(':rate', $j, PDO::PARAM_STR);
	$update->execute();
}
