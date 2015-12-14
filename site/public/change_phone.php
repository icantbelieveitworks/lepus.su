<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

if(empty($_POST['phone'])) die("Empty phone");
if(!ctype_digit($_POST['phone'])) die("Only numeric");
if(strlen($_POST['phone']) > 15) die("too long phone number");
$user['data']['phone'] = $_POST['phone'];
save_user_data($user['id'], $user['data']);
echo 1;
