<?php
if(!empty($_SESSION['sess'])){
	$x = error(auth($_SESSION['id'], $_SESSION['sess']));
	if($x['err'] == 'OK'){
		$user = $x['mes'];
		$user['data'] = json_decode($user['data'], true);
		unset($x);
		if($user['data']['lastIP'] == ip2long($_SERVER["REMOTE_ADDR"])){
			$user['data']['lastIP'] = ip2long($_SERVER["REMOTE_ADDR"]);
			save_user_data($user['id'], $user['data']);
		}
	}else{
		session_unset();
		session_destroy();
		header('refresh: 3; url=http://'.$_SERVER['SERVER_NAME']);
		die($x['err']);
	}
}
