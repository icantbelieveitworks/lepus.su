<?php
//session_set_cookie_params(7200, '/', 'lepus.dev', true, true);
session_start();
echo session_name()."<br/>";
echo $_SESSION['sess'];
