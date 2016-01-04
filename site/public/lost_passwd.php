<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

if(!isset($_GET['hash'])){
	require_once($_SERVER['DOCUMENT_ROOT'].'/private/recaptcha/check.php'); // check only when we create link
	$tmpData = error(lost_passwd($_POST['email']));
	echo json_encode($tmpData);
}else{
	echo lost_passwd_change($_GET['hash']);
}
