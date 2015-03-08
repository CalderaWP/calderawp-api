<?php

/// DEPLOY method - ready
$data = json_decode( file_get_contents( 'php://input' ), true );
if( !empty( $_POST['payload'] ) ){
	
	$payload = json_decode( $_POST['payload'], true );

	/// make stuff
	$data['sender']['login'] = $payload['user'];
	$data['repository']['name'] = $payload['repository']['name'];
	$data['ref'] = 'bitbucket';

}

error_log( 'Starting deploy call' );

// authorized senders
$auths = array(
	'Desertsnowman',
	'dlcramer',
	'Shelob9',
);

// allowed deployments
$deploy = array(
	'iron-circle'	=>	array(
		'bitbucket'		=>	'/var/sites/stage.calderawp.com/wp-content/plugins/iron-circle',
	),
	'calderawp-api'	=>	array(
		'master'		=>	'/var/api/calderawp-api',
	),
	'cwp-theme'		=>	array(
		'master'		=>	'/var/sites/calderawp.com/wp-content/themes/cwp-theme',
		'staging'		=>	'/var/sites/stage.calderawp.com/wp-content/themes/cwp-theme',
	),
	'cwp-shop-front'=>	array(
		'staging'		=>	'/var/sites/stage.calderawp.com/wp-content/themes/cwp-shop-front',
	),
	'Caldera-Forms'	=>	array(
		'current-stable'	=>	'/var/sites/calderawp.com/wp-content/plugins/caldera-forms',
		'1.1.x'				=>	'/var/sites/stage.calderawp.com/wp-content/plugins/caldera-forms',
	),
);



if( in_array( $data['sender']['login'], $auths ) && isset( $deploy[ $data['repository']['name'] ] ) ){

	$path = $deploy[ $data['repository']['name'] ][ basename( $data['ref'] ) ];

	//do the git
	if ( !empty( $path ) ){

		// create git task
		create_task( 'http_deploy', array( $path ) );

		return array('success' => true, 'data' => $data );

	}else{
		error_log( 'Deploy Not set on ' . $data['repository']['name'] . ' for branch ' . basename( $data['ref'] ) );
	}
}


return array( 'success' => false, 'data' => $data );