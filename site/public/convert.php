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



// {"extra":"0","extra_text":"0","extra_currency":"EUR","user":}
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

/*$query = $db2->prepare("SELECT * FROM `servers`");
$query->execute();
while($row=$query->fetch()){
	$row['ip'] = ip2long($row['ip']);
	echo "{$row['id']} => {$row['ip']} => {$row['hostname']} => {$row['point']} => {$row['py_port']} => {$row['py_key']}<br/>";

	$tmpQuery = $db->prepare("INSERT INTO `servers` (`id`, `ip`, `port`, `points`, `domain`, `access`) VALUES (:id, :ip, :port, :points, :domain, :access)");
	$tmpQuery->bindParam(':id', $row['id'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':ip', $row['ip'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':port', $row['py_port'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':points', $row['point'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':domain', $row['hostname'], PDO::PARAM_STR);
	$tmpQuery->bindParam(':access', $row['py_key'], PDO::PARAM_STR);
	$tmpQuery->execute();
} */


// {"extra":"1","extra_text":"дополнительный IP","extra_currency":"EUR"}
$arr = array();
$query = $db2->prepare("SELECT * FROM `params`");
$query->execute();
while($row=$query->fetch()){
	if(in_array($row['billing_id'], $arr))  continue;
	$arr[] = $row['billing_id'];
	
	$tmpQuery = $db2->prepare("SELECT * FROM `params` WHERE `billing_id` = :id");
	$tmpQuery->bindParam(':id', $row['billing_id'], PDO::PARAM_STR);
	$tmpQuery->execute();

	$data = ['extra' => 0, 'extra_text' => 0, 'extra_currency' => 'EUR'];
	
	while($info=$tmpQuery->fetch()){
		if(empty($info['value'])) continue;
		if($info['key'] == 'autoextend' || $info['key'] == 'hostname' || $info['key'] == 'promo' || $info['key'] == 'isp_license' || $info['key'] == 'lastipchange' || $info['key'] == 'ip' || $info['key'] == 'block' || $info['key'] == 'no_isp' || $info['key'] == 'status' || $info['key'] == 'virt' || $info['key'] == 'vps_ip' || $info['key'] == 'os') continue;
		if($info['key'] == 'extra_money' && $info['value'] != 0){
			$info['value'] = round($info['value']/80, 2);
		}
		if($info['key'] == 'extra_money' ){
			$data['extra'] = $info['value'];
		}
		if($info['key'] == 'isp_user'){
			$data['user'] = $info['value'];
		}
		if($info['key'] == 'extra_text'){
			$data['extra_text'] = $info['value'];
		}
		if($info['key'] == 'server_id'){
			$update = $db->prepare("UPDATE `services` SET `server` = :sid WHERE `id` = :id");
			$update->bindParam(':sid', $info['value'], PDO::PARAM_STR);
			$update->bindParam(':id', $row['billing_id'], PDO::PARAM_STR);
			$update->execute();
		}
		//echo "{$info['billing_id']} => {$info['key']} => {$info['value']}<br/>";
	}
	$json = json_encode($data);
	$update = $db->prepare("UPDATE `services` SET `data` = :data WHERE `id` = :id");
	$update->bindParam(':data', $json , PDO::PARAM_STR);
	$update->bindParam(':id', $row['billing_id'], PDO::PARAM_STR);
	$update->execute();
	
	unset($data);
} 
