<?php // как это работает => http://dash.org.ru/pages/merchant.php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/class/easybitcoin.php');

$bitcoin = new Bitcoin($conf['bitcoin_login'], $conf['bitcoin_passwd']); $rate = 30000;
$a = $bitcoin->listtransactions("*", 100000);
for($i=0; count($a) > $i; $i++){
	if($a["$i"]["category"] != "receive" || $a["$i"]["confirmations"] < 6 || $a["$i"]["amount"] < 0.001) continue;
	lepus_update_balance($a["$i"]["txid"], $a["$i"]["address"], intval($a["$i"]["amount"]*$rate), 'bitcoin');
}
