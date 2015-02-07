<?php

/// DEPLOY method - ready
$data = json_decode( file_get_contents( 'php://input' ), true );


// authorized senders
$auths = array(
	'Desertsnowman',
);

// allowed deployments
$deploy = array(
	'calderawp-api'	=>	array(
		'path'			=>	'/var/api/calderawp-api',
		'branch'		=>	'1.0',
	),
	'cwp-theme'		=>	array(
		'path'			=>	'/var/sites/calderawp.com/wp-content/themes/cwp-theme',
		'branch'		=>	'master'
	)
);


if( in_array( $data['sender']['login'], $auths ) && isset( $deploy[ $data['repository']['name'] ] ) ){

	// yup - 
	//do the git
	if ( basename( $data['ref'] ) === $deploy[ $data['repository']['name'] ]['branch'] ){

		exec( "git -C " . $deploy[ $data['repository']['name'] ]['path'] . " pull", $output );
		if( is_array( $output ) ){
			$output = implode("\r\n", $output );
		}
		// log stuff
		//error_log( $output );

		return array('success' => true, 'data' => $data, 'log' => $output );

	}
}


return array( 'success' => false, 'data' => $data );