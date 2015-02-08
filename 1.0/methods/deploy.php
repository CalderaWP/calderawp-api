<?php

/// DEPLOY method - ready
$data = json_decode( file_get_contents( 'php://input' ), true );


// authorized senders
$auths = array(
	'Desertsnowman',
	'Shelob9'
);

// allowed deployments
$deploy = array(
	'calderawp-api'	=>	array(
		'master'		=>	'/var/api/calderawp-api',
	),
	'cwp-theme'		=>	array(
		'master'		=>	'/var/sites/calderawp.com/wp-content/themes/cwp-theme',
		'staging'		=>	'/var/www/wp-content/themes/cwp-theme/',
	),
	'caldera-forms'	=>	array(
		'current-stable'	=>	'/var/sites/calderawp.com/wp-content/plugins/caldera-forms',
		'1.1.x'				=>	'/var/www/wp-content/plugins/caldera-forms',
	),
);


if( in_array( $data['sender']['login'], $auths ) && isset( $deploy[ $data['repository']['name'] ] ) ){

	//do the git
	if ( !empty( $deploy[ $data['repository']['name'] ][ basename( $data['ref'] ) ] ) ){
		error_log( 'Deploying ' . $data['repository']['name'] . ' to ' . $deploy[ $data['repository']['name'] ][ basename( $data['ref'] ) ] );

		exec( "git -C " . $deploy[ $data['repository']['name'] ][ basename( $data['ref'] ) ] . " pull", $output );
		if( is_array( $output ) ){
			$output = implode("\r\n", $output );
		}
		// log stuff
		error_log( $output );

		return array('success' => true, 'data' => $data, 'log' => $output );

	}
}


return array( 'success' => false, 'data' => $data );