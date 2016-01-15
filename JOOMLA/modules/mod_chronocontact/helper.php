<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

//load chronoforms classes
require_once( JPATH_SITE.DS.'components'.DS.'com_chronocontact'.DS.'libraries'.DS.'chronoform.php');
require_once( JPATH_SITE.DS.'components'.DS.'com_chronocontact'.DS.'libraries'.DS.'mails.php');
require_once( JPATH_SITE.DS.'components'.DS.'com_chronocontact'.DS.'libraries'.DS.'customcode.php');
require_once( JPATH_SITE.DS.'components'.DS.'com_chronocontact'.DS.'libraries'.DS.'chronoformuploads.php');
require_once( JPATH_SITE.DS.'components'.DS.'com_chronocontact'.DS.'libraries'.DS.'plugins.php');
require_once( JPATH_SITE.DS.'components'.DS.'com_chronocontact'.DS.'chronocontact.html.php');

class modChronoContactHelper {
	// show online member names
	function getForm($formname) {
	global $mainframe;
	$database =& JFactory::getDBO();	
	$posted = JRequest::get( 'post' , JREQUEST_ALLOWRAW );
	
	$MyModForm =& CFChronoForm::getInstance($formname);
	
	$MyModForm->pagetype = 'module';
	$session =& JFactory::getSession();
	$MyModForm->formerrors = $session->get('chrono_form_errors_'.$formname, '', md5('chrono'));
	if($session->get('chrono_form_data_'.$formname, array(), md5('chrono'))){
		$posted = $session->get('chrono_form_data_'.$formname, array(), md5('chrono'));
		//print_r($posted);
	}
	
	$MyModForm->showForm($formname, $posted);
	}
}
?>