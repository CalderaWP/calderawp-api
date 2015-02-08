<?php

error_log( 'Starting syscheck' );

$out = array(
	'ip'	=>	$_SERVER['REMOTE_ADDR'],
	'server'	=>	$_SERVER['SERVER_NAME']
);

return $out;