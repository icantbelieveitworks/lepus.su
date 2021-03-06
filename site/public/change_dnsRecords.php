<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
if(!is_login()) die("no_login");
if(isset($_POST['id']))
	$dnsPost = explode("_", $_POST['id']);
else
	$dnsPost = explode("_", $_GET['id']);

if(empty($dnsPost[0]) || empty($dnsPost[1])) die("Empty post");

if(!ctype_digit($dnsPost[1])) die("only_num");

$tmpData = lepus_get_dnsRecordAccess($dnsPost[1], $user['id']);
if($tmpData != 'ok') die("deny or no_record or slave");

$tmpCheck = ['name', 'type', 'content', 'prio'];
if(!in_array($dnsPost[0], $tmpCheck)) die("err_check");

if(isset($_GET['load']))
	echo lepus_get_dnsRecord($dnsPost[0], $dnsPost[1]);
else{
	if(!isset($_POST['value'])) die("No post value");
	echo lepus_edit_dnsRecord($dnsPost[0], $dnsPost[1], $_POST['value']);
}
