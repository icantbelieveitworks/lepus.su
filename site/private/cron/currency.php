<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

$i = file_get_contents('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
$xml = simplexml_load_string($i);
foreach($xml->Cube->Cube->Cube as $rate){
	if($rate['currency'] == 'RUB'){
		$j = floatval($rate['rate']);
		if(is_numeric($j)){
			$update = $db->prepare("UPDATE `currency` SET `val` =:rate WHERE `name` = 'EUR2'");
			$update->bindParam(':rate', $j, PDO::PARAM_STR);
			$update->execute();
		}
	}
}
