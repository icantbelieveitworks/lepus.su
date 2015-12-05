<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

if(empty($_POST['email']) || empty($_POST['passwd'])) die("Empty data");
if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) die("Wrong email format");
if(login($_POST['email'], $_POST['passwd']) != 'enter') die("Error");
echo 1;
