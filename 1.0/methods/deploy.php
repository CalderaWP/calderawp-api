<?php

/// DEPLOY method - ready
$data = json_decode( file_get_contents( 'php://input' ), true );

error_log( 'Starting deploy call' );

// authorized senders
$auths = array(
	'Desertsnowman',
	'Shelob9',
);

// allowed deployments
$deploy = array(
	'calderawp-api'	=>	array(
		'master'		=>	'/var/api/calderawp-api',
	),
	'cwp-theme'		=>	array(
		'master'		=>	'/var/sites/calderawp.com/wp-content/themes/cwp-theme',
		'staging'		=>	'/var/sites/stage.calderawp.com/wp-content/themes/cwp-theme',
	),
	'Caldera-Forms'	=>	array(
		'current-stable'	=>	'/var/sites/calderawp.com/wp-content/plugins/caldera-forms',
		'1.1.x'				=>	'/var/sites/stage.calderawp.com/wp-content/plugins/caldera-forms',
	),
);

//
$composers = array(
	'/var/sites/stage.calderawp.com/wp-content/themes/cwp-theme',
	'/var/sites/calderawp.com/wp-content/themes/cwp-theme'
);


if( in_array( $data['sender']['login'], $auths ) && isset( $deploy[ $data['repository']['name'] ] ) ){

	//do the git
	if ( !empty( $deploy[ $data['repository']['name'] ][ basename( $data['ref'] ) ] ) ){

		// create git task
		create_task( 'http_deploy', array( $deploy[ $data['repository']['name'] ][ basename( $data['ref'] ) ] ) );


		return array('success' => true, 'data' => $data, 'log' => $output );

	}else{
		error_log( 'Deploy Not set on ' . $data['repository']['name'] . ' for branch ' . basename( $data['ref'] ) );
	}
}


return array( 'success' => false, 'data' => $data );