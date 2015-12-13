<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

$dnsMaster = NULL;
$_POST['domain'] = idn_to_ascii(mb_strtolower($_POST['domain']));

if(empty($_POST['domain']) || empty($_POST['type'])) die("Empty post");
if(!preg_match('/^[a-zA-Z0-9_.-]+$/', $_POST['domain'])) die("Wrong domain");
if($_POST['type'] == 'slave'){
	if(!filter_var($_POST['master'], FILTER_VALIDATE_IP)) die("Wrong master ip");
	$dnsMaster = $_POST['master'];
}

$tmpData = lepus_addDNSDomain($_POST['domain'], $_POST['type'], $dnsMaster, $user['id']);
if($tmpData == 'already_add') die('We already add this domain');
if($_POST['type'] != 'slave'){
	lepus_add_dnsRecord($_POST['domain'], 'SOA', 'ns2.lepus.su ns1.lepus.su 2012012402 28800 7200 604800 86400', '0', $tmpData);
}
echo $tmpData;
