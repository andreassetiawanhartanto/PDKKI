<?php
// No direct access
defined( '_JEXEC' ) or die();

$nn_file = str_replace( 'nonumberelements', 'nnframework', __FILE__ );

if ( !file_exists( $nn_file ) ) {
	return;
}

// Redirect to new NoNumber! Framework
require_once $nn_file;

class plgSystemNoNumberElements extends plgSystemNNFramework
{
}