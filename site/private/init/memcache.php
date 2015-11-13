<?php
$cache = new Memcache();
$cache->connect($conf['memcache'][0], $conf['memcache'][1]) or die('MEMCACHE ERROR');
