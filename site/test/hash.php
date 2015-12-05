<?php
function rehash($passwd, $hash){
	if(password_needs_rehash($hash, PASSWORD_DEFAULT) || empty($hash))
		return password_hash($passwd, PASSWORD_DEFAULT);
	else
		return 'no_hash';
}

echo rehash(123145);
