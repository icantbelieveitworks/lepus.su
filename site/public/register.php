<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/recaptcha/check.php');

$tmpData = error(lepus_new_account($_POST['email']));
if($tmpData['err'] == 'OK'){
	_mail($_POST['email'], "Регистрация нового аккаунта", "Дорогой клиент,<br/>ваш аккаунт готов.<br/>Ваш логин: {$_POST['email']}<br/>Ваш пароль: {$tmpData['mes']}<br/>Для активации, пожалуйста, авторизуйтесь на нашем сайте.<br/>В противном случае аккаунт будет автоматически удален через 7 дней.");
	$tmpData = ['mes' => 'Мы отправили пароль на ваш email', 'err' => 'OK'];
}
echo json_encode($tmpData);
