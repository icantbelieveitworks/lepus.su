<?php
function lepus_crypt($input, $do = 'encode', $key = 'Jml*Zwde4a#%ix$m'){
	$algo = MCRYPT_RIJNDAEL_256;
	$mode = MCRYPT_MODE_CBC;
	$iv_size = mcrypt_get_iv_size($algo, $mode);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
	switch($do){
		case 'encode':	
		$ciphertext = mcrypt_encrypt($algo, $key, $input, $mode, $iv);
		$ciphertext = $iv . $ciphertext;
		$result = base64_encode($ciphertext);
		break;
		
		case 'decode':
		$ciphertext_dec = base64_decode($input);
		$iv_dec = substr($ciphertext_dec, 0, $iv_size);
		$ciphertext_dec = substr($ciphertext_dec, $iv_size);
		$result = mcrypt_decrypt($algo, $key, $ciphertext_dec, $mode, $iv_dec);
		break;
	}
	return $result;
}

//$i = urlencode(lepus_crypt('12345'));
//echo $i."<br/>";

$j = 'UI7VGKfC0JO7vaWjbrA70J%2BGVfJkNiuyzi1tAVFd5L2Jzlc30Ig%2FyXap89w9fwCv8pleGrb%2FmKi2jpWDLTcRLOG46cOpn%2FWSlQ5QCn30%2FUggMw7logbx6CSyMGLyI5bhn9LRbOAGVLYmLY2e%2FlS6CWeRnA5c%2FlP8k5xlASsv3wK0J%2B%2BRlRIXmDp2%2FbdiSci91x%2FnR%2FTSyMIhvMFeygN4ZQ%3D%3D';
$i = $_GET['hash'];

var_dump($j);
var_dump($i);

//echo $i."<br/>";
//echo lepus_crypt($i, 'decode');

