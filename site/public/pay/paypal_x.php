<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');
	
$paypalemail = "poiuty@lepus.su"; 
$currency    = "RUB";
$postdata=""; 
foreach ($_POST as $key=>$value) $postdata.=$key."=".urlencode($value)."&"; 
$postdata .= "cmd=_notify-validate"; 
$curl = curl_init("https://www.paypal.com/cgi-bin/webscr"); 
curl_setopt ($curl, CURLOPT_HEADER, 0); 
curl_setopt ($curl, CURLOPT_POST, 1); 
curl_setopt ($curl, CURLOPT_POSTFIELDS, $postdata); 
curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0); 
curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 2); 
$response = curl_exec ($curl); 
curl_close ($curl); 
if ($response != "VERIFIED") die("You should not do that ..."); 
if ($_POST['receiver_email'] != $paypalemail || $_POST['txn_type'] != 'web_accept') die("You should not be here ..."); 
if ($_POST['mc_currency'] != $currency) die("only RUB");
echo lepus_update_balance($_POST['txn_id'], $_POST['custom'], $_POST['mc_gross'], 'paypal');
