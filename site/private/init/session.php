<?php
if(!isset($_SESSION)) {
	session_set_cookie_params(0, '/', $_SERVER['SERVER_NAME'], true, true);
	session_start();
}
