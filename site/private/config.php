<?php
$conf['memcache']	= ['127.0.0.1', 11211];
$conf['mysql_host']	= 'localhost';
$conf['mysql_base']	= 'lepus';
$conf['mysql_user']	= 'lepus';
$conf['mysql_pass']	= 'lepus';

$conf['pdns_host'] = 'localhost';
$conf['pdns_base'] = 'lepus';
$conf['pdns_user'] = 'lepus';
$conf['pdns_pass'] = 'lepus';

$conf['robokassa_login'] = 'market';
$conf['robokassa_pass1'] = 'xxxx';
$conf['robokassa_pass2'] = 'xxxx';

$conf['unitpay_id'] = 1;
$conf['unitpay_currency'] = 'RUB';
$conf['unitpay_public'] = '1-xxxx';
$conf['unitpay_secret'] = 'xxxx';

$conf['bitcoin_login'] = 'bitcoinrpc';
$conf['bitcoin_passwd'] = 'xxxx';

$conf['paymaster'] = 'xxxx';
$conf['lepus_crypt'] = 'xxxx';

$conf['telegram'] = 'bot1:xxxx';

$conf['billmgr_user'] = 'xxxx';
$conf['billmgr_pass'] = 'xxxx';

$conf['beard_stats'] = 0;
$conf['beard_stats_api'] = "";

$head_code = "";
if(!empty($conf['beard_stats']))
{ 
	$head_code .= "<script async src=\"https://stats.vboro.de/code/code/{$conf['beard_stats']}/\"></script>"; 
} 