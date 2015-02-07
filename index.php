<?php

$_debug_time = microtime();
$_debug_time = explode( ' ', $_debug_time );
$_debug_time = $_debug_time[1] + $_debug_time[0];
$_debug_start_time = $_debug_time;

// SETUP up the params variable to be passed to the routed file.
global $params;

/// DEFINE ERROR HANDLE
function errorHandler( $errno, $errstr, $errfile, $errline ) {
	error_log( "Error: [$errno] $errstr" );
	throw new Exception;
	die;
}

// load config if exists
$config = array();
if( file_exists( dirname( __FILE__ ) . '/config.json' ) ){
	$is_config = json_decode( file_get_contents( dirname( __FILE__ ) . '/config.json' ), true );
	if( !empty( $is_config ) ){
		$config = $is_config;
		if( !empty( $config['debug'] ) ){
			// Setup error reporting.
			set_error_handler( "errorHandler" );
			ini_set( 'display_errors', 1 );
			error_reporting( E_ALL );
		}
	}
}

// GET URL
$trueuri = ltrim( str_replace( str_replace( trim( $_SERVER['DOCUMENT_ROOT'], '/' ), '', trim( realpath( __dir__ ), '/' ) ), '', $_SERVER['REQUEST_URI'] ), '/' );
$trueuri = preg_replace( "/[\/]{2,}/", '/', $trueuri );

$urlstruct = parse_url( $trueuri );
if( isset( $urlstruct['path'] ) ){
	$urlstruct['path'] = trim( $urlstruct['path'], '/' );
}else{
	$urlstruct['path'] = null;
}

$pathVars = explode( '/',  $urlstruct['path'] );

// API VERSION
if( !empty($pathVars[0]) && file_exists( dirname( __FILE__ ) . '/' . $pathVars[0] .'/' ) ){
	// SOME CCONSTANTS - since the version is true.
	define( 'VERSION', array_shift( $pathVars ) );
}else{
	// version not valid, use default
	if( isset( $config['version'] ) ){
		define( 'VERSION', $config['version'] );
	}else{
		define( 'VERSION', null );
	}
}
// set constants
define( 'ABSPATH', dirname( __FILE__ ) . '/' . VERSION .'/' );
define( 'ABSURL', str_replace( '//', '/', dirname( $_SERVER['SCRIPT_NAME'] ).'/' ) );

// set true URI
$trueuri = ltrim( $urlstruct['path'], VERSION . '/' );

// LOAD ROUTES
$globalLibs  = array();
$globalHeaders  = array();
$globalErrFiles = array();
$globalDebug = false;

$resources = glob( ABSPATH . 'resources/*.json' );
$routes = array();

foreach ( $resources as &$resource ) {	
	$fileData = json_decode( file_get_contents( $resource ), true );
	if ( is_array( $fileData ) ) {
		// is type?
		// DEBUGGING
		foreach ( $fileData as &$set_route ) {

			if ( isset( $set_route['type'] ) ) {
				if ( $set_route['type'] == 'global' ) {
					// include GLOBAL options
					if ( !empty( $set_route['debug'] ) ) {
						$globalDebug = true;
					}
					if ( !empty( $set_route['errors'] ) ) {
						$globalErrFiles = $set_route['errors'];
					}
					if ( !empty( $set_route['libraries'] ) ) {
						$globalLibs = array_merge( $globalLibs, $set_route['libraries'] );
					}
					if ( !empty( $set_route['headers'] ) ) {
						$globalHeaders = array_merge( $globalHeaders, $set_route['headers'] );
					}
				}
			}
		}
		// add to array or routes
		$routes = array_merge( $routes, $fileData );
	}
}

foreach ( $routes as &$try_route ) {

	if ( isset( $try_route['methods'][$_SERVER['REQUEST_METHOD']] ) ) {
		if ( isset( $try_route['version'] ) ) {
			$try_route['name'] = $try_route['version'].'/'.$try_route['name'];
		}
		if ( !empty( $try_route['name'] ) ) {
			$try_route['name'] = trim( $try_route['name'], '/' );
			$routeVars = explode( '/', $try_route['name'] );
		}		
		if ( count( $routeVars ) !== count( $pathVars ) ) {
			
			continue;
		}
		if ( $trueuri == $try_route['name'] ) {
			$route = $try_route;
			break;
		}
		if ( false !== strpos( $try_route['name'], ':' ) ) { //has vars, check if match
			$testUrl = preg_replace( "/\\\:([a-zA-Z0-9_]+)/", '([a-zA-Z0-9_\-\%]+)', preg_quote( $try_route['name'], '/' ) );
			preg_match_all( "/".$testUrl."/", $trueuri, $urlvars );
			if ( empty( $urlvars[1] ) ) {
				continue; //no var match
			}
			$route = $try_route; // var match use route
			preg_match_all( "/:([a-zA-Z0-9_]+)/", $try_route['name'], $routevars );
			$params = array();
			for ( $i=1;$i<count( $urlvars );$i++ ) {$params[] = $urlvars[$i][0];}
			$params = array_combine( $routevars[1], $params );
		}
	}
}


if ( isset( $route ) ) {
	// enable global debug for this route.
	if ( !empty( $globalDebug ) ) {
		$route['debug'] = true;
	}
	// Start Routing
	// wrapped in a try to catch exceptions and to trace errors
	// include libraries first
	try {

		// CHECK METHOD IS ALLOWED
		if ( empty( $route['methods'][$_SERVER['REQUEST_METHOD']] ) ) {
			header( 'HTTP/1.1 405 Method Not Allowed' ); // deny if no method defined for route
			header( 'Allow: '.implode( ', ', array_keys( $route['methods'] ) ), true, 405 );
			if ( !empty( $globalErrFiles['405'] ) ) {
				if ( file_exists( ABSPATH . $globalErrFiles['405'] ) ) {
					include ABSPATH . $globalErrFiles['405'];
					return;
				}
			}
			echo '<h1>405: Method Not Allowed</h1>';
			return;
		}
		// ROUTE FILE
		if ( file_exists( ABSPATH . $route['methods'][$_SERVER['REQUEST_METHOD']]['file'] ) ) {
			ob_start();
			// set output buffer
			// LIBS LOADED WITHIN THE ROUTE TO ALLOW FOR RETURN VALUES ETC.
			// LOAD GLOBAL LIBRARIES
			if ( !empty( $globalLibs ) ) {
				//dump($globalLibs);
				for ( $l=0;$l<count( $globalLibs ); $l++ ) {
					if ( file_exists( ABSPATH . $globalLibs[$l] ) ) {
						if ( empty( $_output ) || $_output === 1 ) { // check that last header did not return;
							$_output = include_once ABSPATH . $globalLibs[$l];
						}
					}
				}
			}
			// LOAD LIBRARIES

			if ( !empty( $route['libraries'] ) ) {
				for ( $l=0;$l<count( $route['libraries'] ); $l++ ) {
					if ( file_exists( ABSPATH . $route['libraries'][$l] ) ) {
						if ( empty( $_output ) || $_output === 1 ) { // check that last header did not return;
							$_output = require_once ABSPATH . $route['libraries'][$l];
						}
					}else {
						echo $route['libraries'][$l].' - gone';
					}
				}
			}
			// LOAD METHOD LIBRARIES
			if ( empty( $_output ) || (int)$_output === 1 ) {
				if ( !empty( $route['methods'][$_SERVER['REQUEST_METHOD']]['libraries'] ) ) {
					for ( $l=0;$l<count( $route['methods'][$_SERVER['REQUEST_METHOD']]['libraries'] ); $l++ ) {
						if ( file_exists( ABSPATH . $route['methods'][$_SERVER['REQUEST_METHOD']]['libraries'][$l] ) ) {
							$_output = require_once ABSPATH . $route['methods'][$_SERVER['REQUEST_METHOD']]['libraries'][$l];
						}
					}
				}
			}
			// Once Libs are loaded - send headers
			//SEND GLOBAL HEADERS
			if ( !empty( $globalHeaders ) ) {
				foreach ( $globalHeaders as $header=>&$value ) {
					header( $header.': '.$value );
				}
			}
			// SEND ROUTE HEADERS
			if ( !empty( $route['headers'] ) ) {
				foreach ( $route['headers'] as $header=>&$value ) {
					// find tags
					if( substr( $value ,0, 1) === '%' && substr( $value ,strlen( $value )-1, 1) === '%'){
						parse_str( str_replace('%', '', $value) , $vars);
						foreach($vars as $type=>$str){
							switch ($type) {
								case 'date':
									$value = str_replace("+0000", "GMT", gmdate('r', strtotime($str)));
									break;
								
								case 'hash':
									if ( empty( $_output ) || (int)$_output === 1 ) { // check that last header did not return;
										if( $str === 'md5' ){
											$value = md5( file_get_contents( ABSPATH . $route['methods'][$_SERVER['REQUEST_METHOD']]['file'] ) );
										}elseif( $str === 'sha1' ){
											$value = md5( file_get_contents( ABSPATH . $route['methods'][$_SERVER['REQUEST_METHOD']]['file'] ) );
										}
									}
									break;
								
								default:
									# code...
									break;
							}
						}
					}

					header( $header.': '.$value, true ); // true to overide any sent by globals
				}
			}
			// SEND METHOD HEADERS
			if ( !empty( $route['methods'][$_SERVER['REQUEST_METHOD']]['headers'] ) ) {
				foreach ( $route['methods'][$_SERVER['REQUEST_METHOD']]['headers'] as $header=>&$value ) {
					// find tags
					if( substr( $value ,0, 1) === '%' && substr( $value ,strlen( $value )-1, 1) === '%'){
						parse_str( str_replace('%', '', $value) , $vars);
						foreach($vars as $type=>$str){
							switch ($type) {
								case 'date':
									$value = str_replace("+0000", "GMT", gmdate('r', strtotime($str)));
									break;

								case 'hash':
									if ( empty( $_output ) || (int)$_output === 1 ) { // check that last header did not return;
										if( $value === 'md5' ){
											$value = md5( file_get_contents( ABSPATH . $route['methods'][$_SERVER['REQUEST_METHOD']]['file'] ) );
										}elseif( $value=='sha1' ){
											$value = md5( file_get_contents( ABSPATH . $route['methods'][$_SERVER['REQUEST_METHOD']]['file'] ) );
										}
									}
									break;
								
								default:
									# code...
									break;
							}
						}
					}

					header( $header.': '.$value, true ); // true to overide any sent by route
					
				}
			}
			if ( empty( $_output ) || (int)$_output === 1 ) { // check that last header did not return;
				$_output = include ABSPATH . $route['methods'][$_SERVER['REQUEST_METHOD']]['file'];
			}

			$buffer = ob_get_clean();
			if ( $_output === 1 || !empty( $buffer ) ) {
				echo $buffer;
			}elseif ( !empty( $_output ) ) {
				if ( is_array( $_output ) || is_object( $_output ) ) {

					header( "Content-Type: application/json charset=UTF-8", true );
					// append version to output
					$_debug_time = microtime();
					$_debug_time = explode( ' ', $_debug_time );
					$_debug_time = $_debug_time[1] + $_debug_time[0];
					$_debug_finish_time = $_debug_time;
					$_debug_total_time = round( ( $_debug_finish_time - $_debug_start_time ), 4 );
					if ( !empty( $route['debug'] ) ) {
						$_output = array_merge( array( '_render_time' => $_debug_total_time, '_version' => VERSION ), $_output );
					}
					echo json_encode( $_output );
				}else {
					echo $_output;
				}
			}
			exit;
		}else {
			if ( !empty( $globalErrFiles['404'] ) ) {
				if ( file_exists( ABSPATH . $globalErrFiles['404'] ) ) {
					include ABSPATH . $globalErrFiles['404'];
					die;
				}
			}
			header( "HTTP/1.1 404 Not Found" );
			echo '<h1>404: page not found</h1>';
		}
	} catch ( Exception $e ) {

		$trace = $e->getTrace();
		if ( !empty( $route['debug'] ) ) {
			header( "Content-Type: text/html charset=UTF-8", true );
			echo '<h2>App Error</h2>';
			echo '<p>'.$trace[0]['args'][1].' on line '.$trace[0]['args'][3].'</p>';
			echo '<p>in file: '.str_replace( __dir__.'/', '', $trace[0]['args'][2] ).'</p>';
		}
		die;
	}
}else {
	try {
		if ( !empty( $globalErrFiles['404'] ) ) {
			if ( file_exists( ABSPATH . $globalErrFiles['404'] ) ) {
				$_output = include ABSPATH . $globalErrFiles['404'];
				if ( is_array( $_output ) || is_object( $_output ) ) {
					header( "Content-Type: application/json charset=UTF-8", true );
					// append version to output
					$_output = array_merge( array( 'version' => VERSION ), $_output );

					echo json_encode( $_output );
				}else {
					echo $_output;
				}
				die;
			}
		}
	} catch ( Exception $e ) {
		header( 'HTTP/1.1 500 Internal Server Error', true );
		echo '<h1>500: Internal Server Error from file: '.basename( __FILE__ ).' </h1>';
		die;
	}
	header( "HTTP/1.1 404 Not Found" );
	echo '<h1>404: '.basename( __FILE__ ).' does not exist</h1>';
}
