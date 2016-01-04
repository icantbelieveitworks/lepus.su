<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

$tmpData = error(login($user['login'], $_POST['passwd']));
if($tmpData['err'] == 'OK'){
	$new_passwd = change_passwd($user['id']);
	_mail($user['login'], "Новый пароль", "Дорогой клиент,<br/>по-вашему запросу, мы поменяли пароль.<br/>Ваш новый пароль: $new_passwd");
	$tmpData = ['mes' => 'Новый пароль отправлен вам на email', 'err' => 'OK'];
}
echo json_encode($tmpData);
