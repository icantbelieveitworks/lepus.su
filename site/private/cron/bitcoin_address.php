<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/class/easybitcoin.php');

$bitcoin = new Bitcoin('bitcoinrpc','EKuoanfcrzWPRWUJThRgS1CK51SvsGHBAA8pqkN5DzMn');
$query = $db->prepare("SELECT * FROM `users` WHERE `bitcoin` IS NULL");
$query->execute();
while($row = $query->fetch()){
	$address = $bitcoin->getnewaddress($row['id']);
	if(!empty($address)){ // if bitcoind down => empty || if bitcoind Verifying blocks... => empty
		$update = $db->prepare("UPDATE `users` SET `bitcoin` =:address WHERE `id` = :id");
		$update->bindParam(':address', $address, PDO::PARAM_STR);
		$update->bindParam(':id', $row['id'], PDO::PARAM_STR);
		$update->execute();
	}
	unset($address);
}
