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
	if($j == "no_user") die("Sorry, but unknow user");
	_mail($_POST['email'], "Забыли пароль?", "Дорогой клиент,\r\n для того, чтобы получить новый пароль,\r\n перейдите ,пожалуйста, по ссылке http://lepus.dev/public/lost_passwd.php?hash=$j");
}else{

}


//if(lost_passwd($_POST['email']) == '1') echo '1';
//	else "Error, please ...";
//_mail("Смена пароля")
