<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

if(empty($_GET['ip']) || empty($_GET['key']) || empty($_GET['name'])) die('empty_post');
$name = mb_strtolower(idn_to_ascii(base64_decode(str_replace(" ", "+", $_GET['name']))));
if (preg_match( '/[^0-9a-zA-Z.-]/', $name)) die('wrong domain');

$query = $db->prepare("SELECT * FROM `users` WHERE `api` = :api");
$query->bindParam(':api', $_GET['key'], PDO::PARAM_STR);
$query->execute();
if($query->rowCount() != 1) die('wrong_key');
$row = $query->fetch();

$tmpData = lepus_addDNSDomain($name, 'slave', $_GET['ip'], $row['id']);
if($tmpData == 'already_add') die('We already add this domain');
if($tmpData == 'wrong_domain') die('Wrong domain');
echo $tmpData;
