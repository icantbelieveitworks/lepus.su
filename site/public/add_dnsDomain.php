<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
if(!is_login()) die("no_login");
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
		$_POST[$val] = idn_to_ascii(mb_strtolower(trim($_POST[$val])));
	}
	$tmpTest = lepus_dnsValid($val, $_POST[$val]);
	if($tmpTest != 'ok') die($tmpTest);
}

$tmpData = lepus_addDNSDomain($_POST['name'], $_POST['type'], $_POST['master'], $user['id']);
if($tmpData == 'already_add') die('We already add this domain');
if($tmpData == 'wrong_domain') die('Wrong domain');
if($_POST['type'] != 'slave'){
	lepus_add_dnsRecord($_POST['name'], 'SOA', "ns1.lepus.su. admin.lepus.su. ".date('Ymd', time())."01 28800 7200 604800 86400", '0', $tmpData);
	lepus_add_dnsRecord($_POST['name'], 'NS', 'ns1.lepus.su', '0', $tmpData);
	lepus_add_dnsRecord($_POST['name'], 'NS', 'ns2.poiuty.com', '0', $tmpData);
}
echo $tmpData;
