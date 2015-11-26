<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

if(empty($_POST['passwd'])) die("Empty data");

if(login($user['login'], $_POST['passwd']) == 'enter'){
	$new_passwd = genRandStr(8);
	change_passwd(password_hash($new_passwd, PASSWORD_DEFAULT), $user['id']);
	_mail($user['login'], "Новый пароль", "Дорогой клиент,\r\nпо-вашему запросу, мы поменяли пароль.\r\nВаш новый пароль: $new_passwd");
	echo "1";
}else{
	echo "Wrong password!";
}
