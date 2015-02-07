<?php

/// DEPLOY method
$data = file_get_contents( 'php://input' );

ob_start();
var_dump( $data );
$out = ob_get_clean();

error_log( $out );

return array('success' => true );