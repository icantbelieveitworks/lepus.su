<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/private/recaptcha/src/autoload.php');
$recaptcha = new \ReCaptcha\ReCaptcha("6LcI6RETAAAAABNCpuHNaZg_miAEYtq-E8oTwkTK");
$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
if (!$resp->isSuccess()) {
	//$errors = $resp->getErrorCodes();
	die(json_encode(error('captcha_fail')));
} 
