<?php
// For backward compatibility

// No direct access
defined( '_JEXEC' ) or die();

require_once str_replace( '/elements/', '/fields/', str_replace( '\\', '/', __FILE__ ) );

if ( version_compare( JVERSION, '1.6.0', 'l' ) ) {
	// For Joomla 1.5
	class JElementSlide extends JElementNN_Slide
	{
	}
} else {
	// For Joomla 1.6
	class JFormFieldSlide extends JFormFieldNN_Slide
	{
	}
}