<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

$dnsMaster = NULL;
if(empty($_POST['domain']) || empty($_POST['type'])) die("Empty post");
if(!preg_match('/^[a-zA-Z0-9_.-]+$/', $_POST['domain'])) die("Wrong domain");
if($_POST['type'] == 'slave'){
	if(!filter_var($_POST['master'], FILTER_VALIDATE_IP)) die("Wrong master ip");
	$dnsMaster = $_POST['master'];
}

$tmpData = lepus_addDNSDomain($_POST['domain'], $_POST['type'], $dnsMaster, $user['id']);
if($tmpData != 1) die('We already add this domain');
echo 1;
