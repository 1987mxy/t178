<?php

require_once( dirname( __FILE__ ) . '/' . '../webim.php' );

define( 'WEBIMDB_DEBUG', true );
define( 'WEBIMDB_CHARSET', 'utf8' );
$imdb = new webim_db( 'root', 'public', 'blog', 'localhost' );
$imdb->set_prefix( 'wp_' );
$imdb->add_tables( array( 'users' ) );

$q = $imdb->prepare( "SELECT * FROM $imdb->users;" );

print_r( $imdb->get_results( $q ) );
//print_r( $imdb->query( $q ) );

