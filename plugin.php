<?php
/**
Plugin Name: CalderaWP API
 */
add_action( 'plugins_loaded', function() {
	if ( class_exists( 'WP_REST_Posts_Controller' ) ) {
		if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
			include_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
			$api_namespace = 'calderawp_api';

			$version = 'v2';
			new \calderawp\calderawp_api\boot( $api_namespace, $version );
		}
	}
});


