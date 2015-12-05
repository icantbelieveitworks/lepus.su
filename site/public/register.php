<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/private/recaptcha/check.php');

if(empty($_POST['email'])) die("Empty data");
if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) die("Wrong email format");
$result = lepus_new_account($_POST['email']);
if($result == 'user_exist') die("User already exist");
_mail($_POST['email'], "Регистрация нового аккаунта", "Дорогой клиент,<br/>ваш аккаунт готов.<br/>Ваш логин: ".$_POST['email']."<br/>Ваш пароль: $result<br/>Для активации, пожалуйста, авторизуйтесь на нашем сайте.<br/>В противном случае аккаунт будет автоматически удален через 7 дней.");
echo 1;
