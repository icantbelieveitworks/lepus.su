<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/private/recaptcha/check.php');

if(!isset($_GET['hash'])){
	if(empty($_POST['email'])) die("Empty data");
	if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) die("Wrong email format");
	$j = lost_passwd($_POST['email']);
	if($j == "no_user") die("Sorry, but unknown user");
	$arr = [$_POST['email'], $j];
	_mail($_POST['email'], "Забыли пароль?", "Дорогой клиент,\r\n для того, чтобы получить новый пароль,\r\n перейдите ,пожалуйста, по ссылке http://lepus.dev/public/lost_passwd.php?hash=".urlencode(lepus_crypt(json_encode($arr))));
}else{
	$i = lost_passwd_change($_GET['hash']);
	if(is_array($i)){
		$new_passwd = genRandStr(8);
		change_passwd(password_hash($new_passwd, PASSWORD_DEFAULT), $i['id']);
		_mail($i['email'], "Новый пароль", "Дорогой клиент,\r\nпо-вашему запросу, мы поменяли пароль.\r\nВаш новый пароль: $new_passwd");
		echo "1";
	}else{
		echo "Unknown user or wrong hash";
	}
}
