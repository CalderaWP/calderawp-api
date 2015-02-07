<?php

/// DEPLOY method

ob_start();
var_dump( $_REQUEST );
$out = ob_get_clean();

error_log( $out );

return array('success' => true );