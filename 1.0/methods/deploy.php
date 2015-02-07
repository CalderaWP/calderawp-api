<?php

/// DEPLOY method - ready
$data = json_decode( file_get_contents( 'php://input' ), true );

ob_start();
var_dump( $data );
$out = ob_get_clean();

error_log( $out );

return array('success' => true );