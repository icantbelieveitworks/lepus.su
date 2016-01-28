<?php
$mac = '00:0a:95:9d:68:1*';
if(!preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/', $mac)) echo "lolka!";
