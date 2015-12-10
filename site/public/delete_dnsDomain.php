<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

if(!isset($_POST['id'])) die("Empty value");

$tmpData = lepus_get_dnsAccess($_POST['id'], $user['id']);
if($tmpData == 'deny') die("Access denied");
lepus_delete_dnsDomain($_POST['id']);
echo 1;
