<?php

function create_task( $task_name, $task_data = null, $args = array() ){
	global $task;


	if( is_array( $task_data ) && !empty( $task_data['task'] ) ){
		console_log( "ERROR: task is a reserved word; Task creation failed" );
		return false;
	}

	$task_id = gen_uuid('mid');
	$new_task = array(
		'id'		=>	$task_id,
		'task'		=>	$task_name,		
	);

	$new_task = array_merge( $new_task, (array) $args );

	if( !empty( $task['id'] ) ){
		$new_task['parent'] = $task['id'];
	}

	if( !empty( $task_data ) ){
		$new_task['params'] = (array) $task_data;
	}else{
		$new_task['params'] = array();
	}
	
	$file_name = $task_id . '.json';

	$task_file = fopen( '/manager/tmp/' . $file_name, "w+" );
	fwrite( $task_file, json_encode( $new_task, JSON_PRETTY_PRINT ) );
	fclose( $task_file );
	// rename stops create event to happen before completed writing data.
	rename( '/manager/tmp/' . $file_name, '/manager/schedule/' . $file_name );
	console_log( "Task '" . $file_name . "' scheduled." );
	return $task_id;
}



function gen_uuid($type='long') {
    if($type == 'long'){
    return strtoupper(sprintf( '%04x%04x-%04x-%04x-%04x%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    ));    
    }elseif($type == 'short'){
        return strtoupper(sprintf( '%04x%04x',

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x0fff ) | 0x8000
    ));
    }elseif($type == 'mid'){
        return strtoupper(sprintf( '%04x%04x-%04x',

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000, mt_rand( 0, 0x0fff ) | 0x8000,
        mt_rand( 0, 0x0fff ) | 0x8000
    ));
    }
}