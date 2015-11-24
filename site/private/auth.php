<?php
if(!empty($_SESSION['sess'])){ 
	$x = error(auth($_SESSION['id'], $_SESSION['sess']));
	if($x['err'] == 'OK'){
		$user = $x['mess']; 
		unset($x);
	}else{
		session_unset();
		session_destroy();
		header('refresh: 3; url=http://lepus.dev');
		die($x['err']);
	}
}
