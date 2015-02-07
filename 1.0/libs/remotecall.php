<?php
/**
 * PHP Remote server caller.
 * 2013- David Cramer
 * Some license I suppose. pick one :D
 */



class RemoteCall {

	/**
	 * Version number
	 */
	var $version = '1.0';

	/**
	 * Default to a 300 second timeout on server calls - thats 5min
	 */
	var $timeout = 300;

	/**
	 * Default to a 8K chunk size
	 */
	var $chunkSize = 8192;

	/**
	 * Default request method
	 */
	var $method = "GET";

	/**
	 * Default data format
	 */
	var $format = "http_build_query";

	/**
	 * Default port
	 */
	var $port = 80;

	/**
	 * Default secure
	 */
	var $secure = false;

	/**
	 * Debug - output payload without sending
	 */
	var $debug = false;

	/**
	 * Debug - output payload without sending
	 */
	var $stream = false;

	/**
	 * Record a change in system separator
	 */
	var $sep_changed = false;

	/**
	 *
	 *
	*/
	var $contentType = 'application/x-www-form-urlencoded';


	function remoteCall( $apiURL, $params = false, $debug=false ) {

		$this->debug = $debug;

		// Setup the request URL
		$this->apiUrl   = parse_url( $apiURL );

		// set a path if url is direct to a domain only
		if ( !isset( $this->apiUrl['path'] ) ) {$this->apiUrl['path'] = '/';}

		// Set the port if available
		if ( !empty( $this->apiUrl['port'] ) ) {
			$this->$port = $this->apiUrl['port'];
		}

		// switch to use ssl:// if https
		if ( strtolower( $this->apiUrl['scheme'] ) == 'https' ) {
			$this->secure = true;
			// set the port to 443 if one has not been set by the url
			if ( !isset( $this->apiUrl['port'] ) ) {
				$this->port = 443;
			}
		}

		// Parse the querystring to an array if there are any
		if ( isset( $this->apiUrl['query'] ) ) {
			parse_str( $this->apiUrl['query'], $query );
		}else {
			$query = array();
		}
		// convert the params to the query string
		if ( is_array( $params ) || is_object( $params ) ) {
			$query = http_build_query( array_merge( $query, $params ) );
		}elseif(!empty($params)){
			if(!empty($query)){
				$query = http_build_query( $query ) .'&'.$params;
			}else{
				$query = $params;
			}
		}
		$this->apiUrl["query"] = $query;
		
	}
	function setTimeout( $seconds ) {
		if ( is_int( $seconds ) ) {
			$this->timeout = $seconds;
		}
	}
	function setMethod( $method ) {
		$this->method = $method;
	}
	function setContentType( $type ) {
		$this->contentType = $type;
	}	
	function setFormat( $format ) {
		switch ( strtolower( $format ) ) {
		case 'json':
			$this->format = 'json_encode';
			break;
		case 'raw':
			$this->format = 'trim';
			break;
		default:
		case 'fields':
			//sigh, apparently some distribs change this to &amp; by default
			if ( ini_get( "arg_separator.output" )!="&" ) {
				$this->sep_changed = true;
				$this->orig_sep = ini_get( "arg_separator.output" );
				ini_set( "arg_separator.output", "&" );
			}
			$this->format = 'http_build_query';
			break;			
		}
	}
	function streamFile( $params = false, $headers = false ) {
		$this->stream = true;
		return $this->callServer( $params, $headers );
	}
	function callServer( $params = false, $headers = false ) {

		$this->errorMessage = "";
		$this->errorCode = "";
		$sep_changed = false;


		// Format the body to be sent
		$format_function = $this->format;

		// set the separator back as not to break things.
		if ( $this->sep_changed ) {
			ini_set( "arg_separator.output", $this->orig_sep );
		}

		// SET UP QUERY VARS IF SET IN GET
		if ( $this->method === 'GET' ) {

			$payloadurl = $this->apiUrl["path"];
			$format_function = 'trim';

			if ( is_array( $params ) || is_object( $params ) ) {
				$params = http_build_query( $params );
			}
			// COMBINE
			if ( !empty( $this->apiUrl["query"] ) && !empty( $params ) ) {
				$payloadurl = $this->apiUrl["path"] . "?" . $this->apiUrl["query"] . '&' . $params;
			}elseif ( empty( $this->apiUrl["query"] ) && !empty( $params ) ) {
				$payloadurl = $this->apiUrl["path"] . "?" . $params;
			}elseif ( !empty( $this->apiUrl["query"] ) && empty( $params ) ) {
				$payloadurl = $this->apiUrl["path"] . "?" . $this->apiUrl["query"];
			}else {
				$payloadurl = $this->apiUrl["path"];
			}


			// null for post vars since GET is url based
			$post_vars = null;

			if ( !empty( $this->debug ) ) {
				//error_log( $payloadurl );
				//ob_start();
				//dump( $this->apiUrl, 0 );
				//error_log( ob_get_clean() );
			}

		}else {
			// KEEP GET VARS in line as not to form part of the POST
			if ( !empty( $this->apiUrl["query"] ) ) {
				$payloadurl = $this->apiUrl["path"] . "?" . $this->apiUrl["query"];
			} else {
				$payloadurl = $this->apiUrl["path"];
			}

			// SET UP QUERY BY STRING OR ARRAY OR OBJECT
			if ( is_array( $params ) || is_object( $params ) ) {
				$post_vars = $format_function( $params );
			}elseif ( !empty( $params ) ) {
				$post_vars = trim( $params );
			}

		}

		$payload = $this->method." " . $payloadurl . " HTTP/1.0\r\n";
		$payload .= "Host: " . $this->apiUrl["host"] . "\r\n";
		$payload .= "User-Agent: CalderaCaller/" . $this->version ."\r\n";
		// Will put in files later- need to read up more on multipart and boundriess.
		$payload .= "Content-type: ".$this->contentType."\r\n";
		// Add in custom headers : usefull for things like api-keys that are used in request headers
		if ( !empty( $headers ) ) {
			foreach ( $headers as $key => $value ) {
				$payload .= $key.": ".$value."\r\n";
			}
		}
		$payload .= "Content-length: " . strlen( $post_vars ) . "\r\n";
		$payload .= "Connection: close \r\n\r\n";
		$payload .= $post_vars;


		// open the connection to output buffer.
		if ( empty( $this->stream ) ) {
			ob_start();
		}
		if ( $this->secure ) {
			$sock = fsockopen( "ssl://".$this->apiUrl["host"], $this->port, $errno, $errstr, 30 );
		} else {
			$sock = fsockopen( $this->apiUrl["host"], $this->port, $errno, $errstr, 30 );
		}
		// return with error messages if there was a porblem.
		if ( !$sock ) {
			$this->errorMessage = "Could not connect (ERR $errno: $errstr)";
			$this->errorCode = "-99";
			if ( !empty( $this->debug ) ) {
				//error_log( $this->errorMessage );
			}
			if ( empty( $this->stream ) ) {
				ob_end_clean();
			}
			return false;
		}
		$response = "";
		fwrite( $sock, $payload );
		stream_set_timeout( $sock, $this->timeout );
		$info = stream_get_meta_data( $sock );
		if ( !empty( $this->debug ) ) {
			////error_log( print_r( $info ) );
		}

		while ( ( !feof( $sock ) ) && ( !$info["timed_out"] ) ) {

			if ( !empty( $this->stream ) ) {
				$response = fread( $sock, $this->chunkSize );
				if ( false !== strpos( $response, "\r\n\r\n" ) ) {
					$stream = explode( "\r\n\r\n", $response, 2 );
					$headers = $this->getHeaders( $stream[0] );
					if ( isset( $headers['Location'] ) ) {
						// Handel a redirect and create a new file stream
						$request = new RemoteCall( $headers['Location'] );
						$request->setMethod( $this->method );
						$request->streamFile( $params, $headers );
						return;
					}
					foreach ( $headers as $header => &$value ) {
						header( $header.': '.$value, true );
					}
					echo $stream[1];
				}else {
					echo $response;
				}
			}else {
				$response .= fread( $sock, $this->chunkSize );
			}
			$info = stream_get_meta_data( $sock );
			if ( !empty( $this->debug ) ) {
				//// error_log( print_r( $info ) );
			}

		}

		fclose( $sock );
		if ( !empty( $this->stream ) ) {
			return;
		}
		// end output buffer
		ob_end_clean();
		if ( !empty( $this->debug ) ) {
			//error_log( $response );
		}

		// return error if a timeout kicked in.
		if ( $info["timed_out"] ) {
			$this->errorMessage = "Could not read response (timed out)";
			$this->errorCode = -98;
			return false;
		}
		// get out the headers
		if ( !empty( $this->stream ) ) {
			//error_log( $response );
		}

		//var_dump( $response );
		list( $headers, $response ) = explode( "\r\n\r\n", $response, 2 );
		$headers = explode( "\r\n", $headers );
		$errored = false;

		foreach ( $headers as $key=>$header ) {
			if ( empty( $key ) ) {
				$h = explode( " ", $header );
				$out['headers']['Status-Code'] = $h[1];
				continue;
			}
			$h = explode( ": ", $header );
			$out['headers'][$h[0]] = $h[1];

		}

		// I do hate magic quotes but some people still have them.
		if ( ini_get( "magic_quotes_runtime" ) ) {
			$response = stripslashes( $response );
		}

		$out['body'] = $response;

		// send out the response.
		if ( empty( $this->stream ) ) {
			return $out;
		}
	}
	private function getHeaders( $headers ) {
		$headers = explode( "\r\n", $headers );
		$errored = false;
		$out = array();
		foreach ( $headers as $key=>$header ) {
			if ( empty( $key ) ) {
				$h = explode( " ", $header );
				$out['Status-Code'] = $h[1];
				continue;
			}
			$h = explode( ": ", $header );
			$out[$h[0]] = $h[1];
		}
		return $out;
	}
}

?>
