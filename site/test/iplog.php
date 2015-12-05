<?php
// http://browscap.org/
//echo $_SERVER['HTTP_USER_AGENT'] . "\n\n";

//$info = get_browser(null, true);
//print_r($info);
// echo $info['platform'];
//if(preg_match('/[^0-9A-Za-z.]/', 'sdfsdfsdfsdf!')) die('error');

echo $_SERVER["REMOTE_ADDR"]."<br/>";
echo ip2long($_SERVER["REMOTE_ADDR"])."<br/>";
echo long2ip(ip2long($_SERVER["REMOTE_ADDR"]));
