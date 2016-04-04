<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

if(!isset($_GET['hash'])){
	require_once($_SERVER['DOCUMENT_ROOT'].'/private/recaptcha/check.php');
	$tmpData = error(lost_passwd(mb_strtolower($_POST['email'])));
	echo json_encode($tmpData);
}else{
	$tmpData = error(lost_passwd_change($_GET['hash']));
	echo lepus_error_page($tmpData['mes']);
}
