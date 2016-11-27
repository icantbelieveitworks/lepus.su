<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

$ip = ip2long($_SERVER['REMOTE_ADDR']);

$query = $db->prepare("SELECT * FROM `ipmanager` WHERE `ip` = :ip AND `sid` = 0");
$query->bindParam(':ip', $ip, PDO::PARAM_STR);
$query->execute();
if($query->rowCount() != 1){
	$query = $db->prepare("SELECT * FROM `servers` WHERE `ip` = :ip");
	$query->bindParam(':ip', $ip, PDO::PARAM_STR);
	$query->execute();
	if($query->rowCount() != 1) die;
}

$pastebin = lepus_pastbinSend(lepus_pastbinLogin(), "Server {$_SERVER['REMOTE_ADDR']} (".date("Y-m-d", time()).")\n\n{$_POST['info']}", "1M");
telegram_send("[HDD monitoring] сервер {$_SERVER['REMOTE_ADDR']}\n$pastebin");

