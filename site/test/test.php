<?php
session_set_cookie_params(7200, '/', 'lepus.dev', true, true);
session_start();
$_SESSION['sess'] = 'auth';
echo $_SESSION['sess'];
?>
