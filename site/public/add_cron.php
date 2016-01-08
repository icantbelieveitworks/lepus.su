<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

if($_POST['do'] == 'remove'){
	$tmpData = error(lepus_addCron($user['id'], 0, 0, $_POST['do'], $_POST['id']));
}else{
	$tmpData = error(lepus_addCron($user['id'], $_POST['time'], $_POST['url'], $_POST['do']));
}
echo json_encode($tmpData);
