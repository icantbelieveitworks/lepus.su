<?php
function clamav_check($file){
	$data = shell_exec('clamdscan --multiscan --fdpass '.escapeshellarg($file));
	$a = ['send' => 'yes', 'info' => 'none'];
	if(strpos($data, 'Infected files: 1') !== FALSE){
		$i = explode("\n", $data);
		$j = explode(":", $i[0]);
		$a = ['send' => 'no', 'info' => trim($j[1])];		
	}else{
		$data = file_get_contents($file);
		if(count(file($file)) < 10 && strlen($data) > 10000){ // обычно такой скрипт это 1-10 строчек и много много символов.
			$a = ['send' => 'no', 'info' => 'possible spam bot'];	
		}
	}
	unlink($file);
	return json_encode($a);
}

function clamav_start(){
	$base = '/var/www/tmp';
	$hash = hash('sha256', file_get_contents($_FILES["file"]["tmp_name"]));
	//$name_dir = substr($hash, 0, 2);
	//if(!is_dir("$base/$name_dir")){
	//	mkdir("$base/$name_dir");
	//}
	move_uploaded_file($_FILES["file"]["tmp_name"], "$base/$hash");
	return clamav_check("$base/$hash");
}

if(!empty($_POST) && !empty($_FILES)) echo clamav_start();
