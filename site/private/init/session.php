<?php
if(!isset($_SESSION)) {
	session_set_cookie_params(0, '/', 'lepus.dev', true, true);
	session_start();
}
