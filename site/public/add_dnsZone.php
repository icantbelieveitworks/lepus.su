<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

if(!isset($_POST['zone']) || !isset($_POST['type']) || !isset($_POST['data']) || !isset($_POST['prio']) || !isset($_POST['domain_id'])) die("Empty POST value");
if(empty($_POST['type']) || empty($_POST['domain_id'])) die("Empty value");
if(!ctype_digit($_POST['prio']) || !ctype_digit($_POST['domain_id'])) die("only_num");
if(strlen($_POST['zone']) > 255) die("max name strlen 255");
if(strlen($_POST['zone']) < 1 || strlen($_POST['data']) < 1 || strlen($_POST['prio']) < 1) die("Empty zone or data value");

$tmpData = lepus_get_dnsAccess($_POST['domain_id'], $user['id'], 'check');
if($tmpData == 'deny' || $tmpData == 'SLAVE') die("deny or no_record or slave ".$tmpData);
echo lepus_add_dnsRecord(idn_to_ascii(mb_strtolower($_POST['zone'])), $_POST['type'], idn_to_ascii(mb_strtolower($_POST['data'])), $_POST['prio'], $_POST['domain_id']);
