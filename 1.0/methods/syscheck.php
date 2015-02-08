<?php

$out = array(
	'ip'	=>	$_SERVER['REMOTE_ADDR'],
	'server'	=>	$_SERVER['SERVER_NAME']
);

return $out;