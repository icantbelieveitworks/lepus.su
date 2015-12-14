<?php
// https://lepus.dev/test/getwhile.php?id=24234&test=ssdfsdf
// id => 24234
// test => ssdfsdf
//foreach($_GET as $key => $val){
//	echo "$key => $val<br/>";
//}



$tmpGET = ['zone', 'type', 'content', 'prio', 'domain_id'];
foreach($tmpGET as $val){
	if(!isset($_GET[$val])) die("Empty GET value");
}
