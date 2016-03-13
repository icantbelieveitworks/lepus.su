<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

try {
	$db2 = new PDO("mysql:host=localhost;dbname=old", "root", "f8bb257b5065911eacb5bac33c4ad454");
	$db2->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$db2->exec("set names utf8");
}
catch(PDOException $e) {
	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/private/logs/PDOErrors.txt', $e->getMessage().PHP_EOL, FILE_APPEND);
	die('MySQL ERROR');
}

/*
//{"balance":0,"phone":"44444444444","regDate":1451867590,"access":1,"lastIP":null}
$query = $db2->prepare("SELECT * FROM `users` WHERE `status` = 1");
$query->execute();
while($row=$query->fetch()){
	echo "{$row['user_id']} => {$row['mail']} => {$row['phone']} => {$row['register']} => {$row['password']} => {$row['money']} => {$row['dns_key']}<br/>";

	$arr = ['balance' => $row['money'], 'phone' => $row['phone'], 'regDate' => $row['register'], 'access' => 1, 'lastIP' => null];
	$json = json_encode($arr);
	
	$tmpQuery = $db->prepare("INSERT INTO `users` (`id`, `login`, `passwd`, `api`, `data`) VALUES (:id, :login, :passwd, :api, :data)");
	$tmpQuery->bindParam(':id', $row['user_id'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':login', $row['mail'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':passwd', $row['password'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':api', $row['dns_key'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':data', $json, PDO::PARAM_STR);
	$tmpQuery->execute();
} */


// {"extra":"1","extra_text":"дополнительный IP","extra_currency":"EUR"}
/*$query = $db2->prepare("SELECT * FROM `billing`");
$query->execute();
while($row=$query->fetch()){
	echo "{$row['id']} => {$row['service_id']} => {$row['user_id']} {$row['create_time']} => {$row['paid_time']}<br/>";
	$tmpQuery = $db->prepare("INSERT INTO `services` (`id`, `sid`, `uid`, `time1`, `time2`) VALUES (:id, :sid, :uid, :time1, :time2)");
	$tmpQuery->bindParam(':id', $row['id'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':sid', $row['service_id'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':uid', $row['user_id'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':time1', $row['create_time'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':time2', $row['paid_time'], PDO::PARAM_STR);
	$tmpQuery->execute();
}*/

