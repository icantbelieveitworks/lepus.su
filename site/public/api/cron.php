<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

if(@$_GET['passwd'] != md5('secret')) die;

$data = array();
$query = $db->prepare("SELECT * FROM `cron`");
$query->execute();
while($row=$query->fetch()){
	if(empty($data[$row['uid']]['max'])){
		$tmpQuery = $db->prepare("SELECT MAX(date) FROM `cron` WHERE `uid` = :uid");
		$tmpQuery->bindParam(':uid', $row['uid'], PDO::PARAM_STR);
		$tmpQuery->execute();
		$tmpRow = $tmpQuery->fetch();
		$data[$row['uid']]['max'] = $tmpRow["MAX(date)"];
	}
	$data[$row['uid']][] = ['time' => $row['time'], 'url' => $row['url']];
}

echo json_encode($data);
