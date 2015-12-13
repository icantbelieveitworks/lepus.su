<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

if(!isset($_POST['id'])) die("Empty value");
$tmpData = lepus_get_dnsRecordAccess($_POST['id'], $user['id']);
if($tmpData != 'ok') die("deny or no_record or slave");
lepus_delete_dnsRecord($_POST['id']);
echo 1;
