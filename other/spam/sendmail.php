#!/usr/bin/php
<?php
// about sendmail flags => http://linux.die.net/man/8/sendmail.sendmail
// clamav scan php mail script => if get *virus* => don`t send mail
setlocale(LC_CTYPE, "en_US.UTF-8");

function log_in_file($data){
	file_put_contents('/home/mail-clamav.log', $data.PHP_EOL, FILE_APPEND | LOCK_EX);
}

function send_debug($data){
	return var_export($data, true);
}

function lepus_get_script($data, $j=0){
	foreach($data as $value){
		$k = explode(":", $value);
		if($k[0] == "To"){
			$email = $k[1];
			break;
		}
		$j++;
		if($j > 4) break;
	}
	if(empty($email)) return 'no_email';
	$last = explode(":", shell_exec('cat /home/spam/mail.log | grep '.escapeshellarg(trim($email)).' | sed -e \'s/mail() on \[\(.*\)]\(.*\) /\1/\' | tail -n 1'));
	return trim($last[0]);
}

function lepus_send($filename){
	$target_url = 'http://spam.lepus.su/index.php';
	if(PHP_VERSION_ID < 50500)
		$postdata = array('method' => 'post', 'file' => '@'.$filename);
	else
		$postdata = array('method' => 'post', 'file' => new CurlFile($filename));

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$target_url);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

$pointer = fopen('php://stdin', 'r');
while ($line = fgets($pointer)) {
	$data[] = $line;
	$mail .= $line;
}

$script = explode(':', $data[2]);
$mail_script = trim($script[2]);
$script_path = trim($_SERVER['PWD'].'/'.$mail_script);

// |===================================|
// |  normal script sendmail           |
// |  0 => 'X-PHP-Originating-Script', |
// |  1 => ' 0',                       |
// |  2 => 'test.php                   |
// |-----------------------------------|
// |  eval sendmail => 100% spambot    |
// |  0 => 'X-PHP-Originating-Script', |
// |  1 => ' 536',                     |
// |  2 => 'utf96.php(1976) ',         |
// |  3 => ' eval()\'d code            |
// |===================================|
if(strpos($script[3], 'eval') !== FALSE){
	log_in_file("$script_path => eval => block");
	die;
}

// we cant get real script path =>  if send mail in include file, example
// /file1.php => require_once('file2.php');
// /inc/file2.php => mail("poiuty@lepus.su", "My Subject", "Line 1\nLine 2\nLine 3");
// trim($_SERVER['PWD'].'/'.$mail_script); => !! /file2.php !! => not /inc/file2.php
// but we can get this info from mail.log
if(!file_exists($script_path)){
	log_in_file("$script_path => file not exists  => check again");
	$script_path = lepus_get_script($data);
	// /var/www/xxx/data/www/xxx.com/plugins/user/joomla/utf96.php(1976) we need remove all after last .php
	$script_path  = substr($script_path, 0, strrpos($script_path, '.php') + 4); 
	log_in_file("$script_path => get new script");
	if(!file_exists($script_path)){
		log_in_file("$script_path => file not exists  => block");
		die;
	}
	if(strpos($script_path, $mail_script) === FALSE){
		log_in_file("wrong $script_path => $mail_script => block");
		die;
	}
}
if(empty($script_path)){
	log_in_file("empty script_path => block");
	die;
}

$test = json_decode(lepus_send($script_path), true);
if($test['send'] == 'yes'){ // let`s send mail
	$command = 'echo '.escapeshellarg($mail).' | /usr/sbin/exim -t -i -f '.escapeshellarg($_SERVER['argv']['4']);
	shell_exec($command);
	log_in_file("$script_path => OK => ".$test['info']);
}else{
	log_in_file("$script_path => block => ".$test['send']." ".$test['info']);
}
