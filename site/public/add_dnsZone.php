<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

//zone type data prio domain_id
if(!isset($_POST['zone']) || !isset($_POST['type']) || !isset($_POST['data']) || !isset($_POST['prio']) || !isset($_POST['domain_id'])) die("Empty value");
if(!ctype_digit($_POST['prio']) || !ctype_digit($_POST['domain_id'])) die("only_num");

$tmpData = lepus_get_dnsAccess($_POST['domain_id'], $user['id']);
if($tmpData == 'deny') die("Access denied");
echo lepus_add_dnsRecord($_POST['zone'], $_POST['type'], $_POST['data'], $_POST['prio'], $_POST['domain_id']);
