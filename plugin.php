<?php
/**
Plugin Name: CalderaWP API
 */

if (  defined( 'REST_API_VERSION' ) &&  version_compare( '2.0-beta2', REST_API_VERSION, '>=' ) )  {
	if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
		include_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
		$api_namespace = 'calderawp_api';

		$version = 'v2';
		new \calderawp\calderawp_api\boot( $api_namespace, $version );
	}
}


