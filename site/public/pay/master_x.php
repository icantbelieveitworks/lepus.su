<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/init/memcache.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/private/func.main.php');

$a = $_POST['LMI_MERCHANT_ID'].';'.@$_POST['LMI_PAYMENT_NO'].';'.$_POST['LMI_SYS_PAYMENT_ID'].';'.$_POST['LMI_SYS_PAYMENT_DATE'].';'.$_POST['LMI_PAYMENT_AMOUNT'].';'.$_POST['LMI_CURRENCY'].';'.$_POST['LMI_PAID_AMOUNT'].';'.$_POST['LMI_PAID_CURRENCY'].';'.$_POST['LMI_PAYMENT_SYSTEM'].';'.@$_POST['LMI_SIM_MODE'].';'.$conf['paymaster'];
if (@$_POST['LMI_PREREQUEST']==1) die("YES");
if(base64_encode(hash('sha256', $a, true)) != $_POST['LMI_HASH']) die;
echo lepus_update_balance($_POST['LMI_SYS_PAYMENT_ID'], $_POST['LEPUS_USER'], $_POST['LMI_PAYMENT_AMOUNT'], 'paymaster');
