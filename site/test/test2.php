<?php
function test($handler){
	switch($handler){
		default: return 'no_handler'; break;
		case 'yyy': $x = 5; break;
		case 'xxx': $x = 10; break;
	}
	return $x;
}

echo test('1111');
