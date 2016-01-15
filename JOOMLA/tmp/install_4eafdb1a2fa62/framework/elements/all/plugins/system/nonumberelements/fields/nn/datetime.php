<?php
// No direct access
defined( '_JEXEC' ) or die();

$nn_file = str_replace( '/nn/', '/', str_replace( '\\', '/', __FILE__ ) );

if ( !file_exists( $nn_file ) ) {
	return;
}

// Load common functions
require_once $nn_file;