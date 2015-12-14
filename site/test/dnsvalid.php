<?php
function lepus_dnsValid($type, $value, $j = 'ok'){
	$i = ['prio' => 3, 'name' => 255];
	//if(!array_key_exists($type, $i)) return 'no_exist'; => https://poiuty.com/img/09/d14e1800b49b40974a2e3cba6da3e209.png
	if(isset($i[$type])){
		if(strlen($value) > $i[$type]) $j = 'error';
	}else{
		$j = 'no_exist';
	}
	return $j;
}

echo lepus_dnsValid('prio3', '11223')."<br/>"; // => no_exist
echo lepus_dnsValid('prio', '1123')."<br/>"; // => error
echo lepus_dnsValid('prio', '112')."<br/>"; // => ok
