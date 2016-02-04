<?php
/*session_set_cookie_params(7200, '/', 'lepus.dev', true, true);
session_start();
$_SESSION['sess'] = 'auth';
echo $_SESSION['sess'];*/


/*function error($message, $j = 0){
	if(!is_array($message)){
		$err = [
			"no_auth" => "Неудачная попытка входа.",
			"no_user" => "Неправильный логин.",
			"bad_passwd" => "Неправильный пароль.",
			"block_user" => "Пользователь заблокирован",
			"empty_message" => "Пустое сообщение"
		];
		if (array_key_exists($message, $err)) $j = 1;
	}
	
	if($j == 1){
		$message = ['mes' => $message, 'err' => $err[$message]];
	}else{
		$message = ['mes' => $message, 'err' => 'OK'];
	}
	
	return $message;
}

var_dump(error('empty_message'));*/

function domain_check3rd($domain){
	$arr = explode(".", $domain);
	if(count($arr) > 2){
		var_dump($arr);
	}
}

$a = 'test.ru';
$b = 'xxx.test.ru';

//array_reverse
//domain_check3rd($a);
//domain_check3rd($b);

$arr = array_reverse(explode(".", $b));
var_dump($arr);
