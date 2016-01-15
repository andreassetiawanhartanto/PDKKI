<?php
defined('_JEXEC') or die('Restricted access'); 

$formname = "ChronoContact_".$MyForm->formrow->name;
$script .= "";

$val_array = array('val_required' => 'required',
	'val_validate_number' => 'validate-number',
	'val_validate_digits' => 'validate-digits',
	'val_validate_alpha' => 'validate-alpha',
	'val_validate_alphanum' => 'validate-alphanum',
	'val_validate_date' => 'validate-date',
	'val_validate_email' => 'validate-email',
	'val_validate_url' => 'validate-url',
	'val_validate_date-au' => 'validate-date-au',
	'val_validate_currency-dollar' => 'validate-currency-dollar',
	'val_validate_selection' => 'validate-selection',
	'val_validate_one-required' => 'validate-one-required'
);
foreach ( $val_array as $k => $v ) {
	$temp = str_replace(" ", "", $MyForm->formparams($k));
	if ( $temp ) { 
		$script .= "('$temp').split(',').each( function(field) {
			sel = '[name='+field+']';
			if ($('$formname').getElement(sel) != null ) {
				$('$formname').getElement(sel).addClass('$v');
			}
		});";
	} 
}
/////

if ( str_replace(" ", "", $MyForm->formparams('val_validate_confirmation')) ) { 
	$required_fields = explode(",", str_replace(" ", "", $MyForm->formparams('val_validate_confirmation')));
	foreach ( $required_fields as $required_field ) {
		$field = explode("=", $required_field, 2);
			if ( count($field) == 2 ) {
			$script .= "
var f_message = 'Please make sure that the 2 fields are matching';
sel   = '[name=".$field[1]."]';
sel_0 = '[name=".$field[0]."]';
if ( $('$formname').getElement(sel) != null && $('$formname').getElement(sel_0) != null ) {
	if ( $('$formname').getElement(sel).getProperty('title') ) {
		f_message = $('$formname').getElement(sel).getProperty('title');
	}
	var cfvalidate_".$field[1]." = new LiveValidation($('$formname').getElement(sel), { 
		validMessage: ' ' 
	});
	cfvalidate_".$field[1].".add( Validate.Confirmation, { 
		match :$('$formname').getElement(sel_0), 
		failureMessage: f_message 
	});
}
			";
		}
	}
}

if ( $script ) {
	$script = "window.addEvent('domready', function() { 
	var sel = null;
	var sel_0 = null;	
	$script
	});";
	$doc =& JFactory::getDocument();
	$doc->addScriptDeclaration($script);
}
?>