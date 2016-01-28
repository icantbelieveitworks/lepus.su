<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

foreach($_POST as $key => $value){
	if(empty($value)){
		$tmpData = error('empty_post_value');
		break;
	}
	if(strlen($value) > 30){
		$tmpData = error('too_long_value');
		break;
	}
	if($key == 'ip' && !filter_var($value, FILTER_VALIDATE_IP)){
		$tmpData = error('wrong_ip');
		break;
	}
	if($key == 'mac' && !preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/', $value)){
		$tmpData = error('wrong_mac');
		break;
	}
	if($key == 'host' && preg_match('/[^0-9a-z.]/', $value)){
		$tmpData = error('wrong_host');
		break;
	}
	if(($key == 'server' || $key == 'user') && !ctype_digit($value)){
		$tmpData = error('only_numeric');
		break;
	}
}

if(empty($tmpData)){
	if($user['data']['access'] < 2){
		 $tmpData = error('no_access');
	}else{
		$tmpData = error(lepus_admin_addIP(ip2long($_POST['ip']), $_POST['mac'], $_POST['host'], $_POST['server'], $_POST['user']));
	}
}
echo json_encode($tmpData);
