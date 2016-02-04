<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');

switch(@$_POST['type']){
	default:
		$_POST['master'] == NULL;
		$tmpPOST = ['name', 'type'];
	break;
	case 'slave':
		$tmpPOST = ['name', 'type', 'master'];
	break;
}
	
foreach($tmpPOST as $val){
	if(!isset($_POST[$val])) die("Empty POST value");
	if($val == 'name'){
		$_POST[$val] = idn_to_ascii(mb_strtolower($_POST[$val]));
	}
	$tmpTest = lepus_dnsValid($val, $_POST[$val]);
	if($tmpTest != 'ok') die($tmpTest);
}

$tmpData = lepus_addDNSDomain($_POST['name'], $_POST['type'], $_POST['master'], $user['id']);
if($tmpData == 'already_add') die('We already add this domain');
if($tmpData == 'wrong_domain') die('Wrong domain');
if($_POST['type'] != 'slave'){
	lepus_add_dnsRecord($_POST['name'], 'SOA', 'ns2.lepus.su ns1.lepus.su 2012012402 28800 7200 604800 86400', '0', $tmpData);
}
echo $tmpData;
