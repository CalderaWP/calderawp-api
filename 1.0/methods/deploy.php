<?php



ob_start();
var_dump( $_POST );
$out = ob_get_clean();

error_log( $out );

return array('success' => true );