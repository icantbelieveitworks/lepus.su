<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

if(!isset($_GET['hash'])){
	require_once($_SERVER['DOCUMENT_ROOT'].'/private/recaptcha/check.php'); // check only when we create link
	if(empty($_POST['email'])) die("Empty data");
	if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) die("Wrong email format");
	$j = lost_passwd($_POST['email']);
	if($j == "no_user") die("Sorry, but unknown user");
	$arr = [$_POST['email'], $j, time()+60*60*24];
	_mail($_POST['email'], "Забыли пароль?", "Дорогой клиент,<br/>после того как вы перейдете <a href=\"http://lepus.dev/public/lost_passwd.php?hash=".urlencode(lepus_crypt(json_encode($arr)))."\">по этой ссылке</a> - вы получите второе письмо с паролем от вашего аккаунта.<br/>");
	echo 1;
}else{
	$i = lost_passwd_change($_GET['hash']);
	if(is_array($i)){
		if(time() > $i['time']){
			header('refresh: 3; url=https://lepus.dev');
			die("Ссылка устарела, для восстановления пароля - получите новую ссылку.");
		}
		$new_passwd = genRandStr(8);
		change_passwd(password_hash($new_passwd, PASSWORD_DEFAULT), $i['id']);
		_mail($i['email'], "Новый пароль", "Дорогой клиент,<br/>по-вашему запросу, мы поменяли пароль.<br/>Ваш новый пароль: $new_passwd");
		header('Location: http://lepus.dev');
	}else{
		echo "Unknown user or wrong hash";
	}
}
