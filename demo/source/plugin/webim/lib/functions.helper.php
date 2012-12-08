<?php

/**
 * Provide a simple method for get data from the $_GET param and $_POST data.
 * Ignore the method type beacuse the request maybe come from ajax or jsonp.
 *
 */

function webim_gp( $key, $default = null ) {
	if( isset( $_GET[$key] ) ) {
		$v = $_GET[$key];
	} elseif ( isset( $_POST[$key] ) ) {
		$v = $_POST[$key];
	} else {
		$v = $default;
	}
	return $v;
}

/**
 * Convert string ids to array
 *
 * @param string $ids
 *
 * @return array ids
 *
 */


function webim_ids_array( $ids ){
	return ($ids===NULL || $ids==="") ? array() : (is_array($ids) ? array_unique($ids) : array_unique(explode(",", $ids)));
}

/**
 * Validate param presence
 *
 */

function webim_validate_presence() {
	$keys = func_get_args();
	$invalid_keys = array();
	foreach( $keys as $key ) {
		$val = webim_gp( $key );
		if ( !$val || !trim( $val )  ) 
			$invalid_keys[] = $key;
	}
	if( $invalid_keys ) {
		header( "HTTP/1.0 400 Bad Request" );
		exit( "Empty get " . implode( ",", $invalid_keys ) );
	}
}


/** 
 * url helper 
 */

function webim_is_remote() {
	$remote = false;
	if ( strlen($_SERVER['HTTP_REFERER']) ) {
		$referer = parse_url( $_SERVER['HTTP_REFERER'] );
		$referer['port'] = isset( $referer['port'] ) ? $referer['port'] : "80";
		if ( $referer['port'] != $_SERVER['SERVER_PORT'] || $referer['host'] != $_SERVER['SERVER_NAME'] || $referer['scheme'] != ( (@$_SERVER["HTTPS"] == "on") ? "https" : "http" ) ){
			$remote = true;
		}
	}
	return $remote;
}

function webim_urlpath() {
	global $_SERVER;
	$name = htmlspecialchars($_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF']);
	return ( (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://" ) . ( ( $_SERVER["SERVER_PORT"] != "80" ) ? ( $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"] ) : $_SERVER["SERVER_NAME"] ) . substr( $name, 0, strrpos( $name, '/' ) ) . "/";
}

/**
 * Wrap the result
 */

function webim_callback( $data, $jsonp = "callback" ){
	$data = json_encode( $data );
	return webim_gp( $jsonp ) ? ( webim_gp( $jsonp ) . "($data);" ) : $data;
}

/**
 * DB helper
 *
 */

define( "WEBIM_HISTORY_KEYS", "`to`,`nick`,`from`,`style`,`body`,`type`,`timestamp`" );

/**
 * Get history message
 *
 * @param string $with chat
 * @param string $type unicast or multicast
 * @param int    $limit history num
 *
 * Example:
 *
 * 	webim_get_history( 'susan', 'unicast' );
 *
 */

function webim_get_history( $with, $type = 'unicast', $limit = 30 ) {
	global $imdb, $imuser;

	if ( !$imdb || !$imuser )
		return false;

	$user_id = $imuser->id;

	if( $type == "unicast" ){
		$query = $imdb->prepare( "SELECT " . WEBIM_HISTORY_KEYS . " FROM $imdb->webim_histories  
			WHERE `type` = 'unicast' 
			AND ((`to`=%s AND `from`=%s AND `fromdel` != 1) 
			OR (`send` = 1 AND `from`=%s AND `to`=%s AND `todel` != 1))  
			ORDER BY timestamp DESC LIMIT %d", $with, $user_id, $with, $user_id, $limit );
	} else {
		$query = $imdb->prepare( "SELECT " . WEBIM_HISTORY_KEYS . " FROM $imdb->webim_histories  
			WHERE `to`=%s AND `type`='multicast' AND send = 1 
			ORDER BY timestamp DESC LIMIT %d", $with, $limit );
	}

	return array_reverse( $imdb->get_results( $query ) );
}

/**
 * Clear user history message
 *
 * @param string $with chat user
 *
 */

function webim_clear_history( $with ) {
	global $imdb, $imuser;

	if ( !$imdb || !$imuser )
		return false;

	$imdb->update( $imdb->webim_histories, array( "fromdel" => 1, "type" => "unicast" ), array( "from" => $imuser->id, "to" => $with ) );
	$imdb->update( $imdb->webim_histories, array( "todel" => 1, "type" => "unicast" ), array( "to" => $imuser->id, "from" => $with ) );
	$imdb->query( $imdb->prepare( "DELETE FROM $imdb->webim_histories WHERE todel=1 AND fromdel=1" ) );
}

/**
 * Clear user history message
 *
 * @param string $type unicast or multicast
 * @param string $to message receiver
 * @param string $body message
 * @param string $style css
 * @param int $send is 0 when the receiver is offline
 *
 */

function webim_insert_history( $type, $to, $body, $style, $send = 1 ) {
	global $imdb, $imuser;

	if ( !$imdb || !$imuser )
		return false;

	$imdb->insert( $imdb->webim_histories, array(
		"send" => $send,
		"type" => $type,
		"to" => $to,
		"from" => $imuser->id,
		"nick" => $imuser->nick,
		"body" => $body,
		"style" => $style,
		"timestamp" => webim_microtime_float() * 1000,
		"created_at" => date( 'Y-m-d H:i:s' ),
	) );
}

/**
 * Get new message
 *
 */

function webim_new_message( $limit = 50 ) {
	global $imdb, $imuser;

	if ( !$imdb || !$imuser )
		return false;

	$query = $imdb->prepare( "SELECT " . WEBIM_HISTORY_KEYS . " FROM $imdb->webim_histories  
		WHERE `to`=%s and send != 1 
		ORDER BY timestamp DESC LIMIT %d", $imuser->id, $limit );

	return array_reverse( $imdb->get_results( $query ) );
}

/**
 * mark the new message as read.
 *
 */

function webim_new_message_to_histroy() {
	global $imdb, $imuser;

	if ( !$imdb || !$imuser )
		return false;

	$imdb->update( $imdb->webim_histories, array( "send" => 1 ), array( "to" => $imuser->id, "send" => 0 ) );
}

/**
 * Get user setting
 *
 * @return object settings
 *
 */

function webim_get_settings( $type = 'web' ) {
	global $imdb, $imuser;

	if ( !$imdb || !$imuser )
		return false;

	$data = $imdb->get_var( $imdb->prepare( "SELECT " . $type . " FROM $imdb->webim_settings WHERE uid = %d", $imuser->uid ) );

	if( $data ){
		return json_decode( $data );
	} else {
		$imdb->insert( $imdb->webim_settings, array( "uid" => $imuser->uid, $type => "{}" ) );
		return new stdClass();
	}
}

function webim_update_settings( $data, $type = 'web' ) {
	global $imdb, $imuser;

	if ( !$imdb || !$imuser )
		return false;

	if( $data ) {
		if ( !is_string( $data ) ){
			$data = json_encode( $data );
		}
		$imdb->update( $imdb->webim_settings, array( $type => $data ), array( 'uid' => $imuser->uid ) );
	}
}

/** Simple function to replicate PHP 5 behaviour */

function webim_microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

?>
