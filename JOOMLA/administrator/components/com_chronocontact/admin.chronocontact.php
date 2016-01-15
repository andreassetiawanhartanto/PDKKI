<?php
/*
/**
* CHRONOFORMS version 3.0 
* Copyright (c) 2008 Chrono_Man, ChronoEngine.com. All rights reserved.
* Author: Chrono_Man (ChronoEngine.com)
* See readme.html.
* @license		GNU/GPL
* Visit http://www.ChronoEngine.com for regular update and information.
**/

/* ensure that this file is not called from another file */
defined('_JEXEC') or die('Restricted access'); 

//global $mosConfig_lang, $mosConfig_absolute_path, $chronocontact_params;
// Loading of the database class and the HTML class
require_once( JApplicationHelper::getPath( 'admin_html' ) ); 
require_once( JApplicationHelper::getPath( 'class' ) );

$id 	= JRequest::getVar( 'id', '', 'get', 'int', 0 );
//$task 	= JRequest::getVar( 'task', '', 'post', 'string', '' );
$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array');
if (!is_array( $cid )) {
	$cid = array(0);
}
$cid_del 	= JRequest::getVar( 'cid_del', array(), 'post', 'array');
if (!is_array( $cid_del )) {
	$cid_del = array(0);
}
?>
<?php echo JHTML::_('behavior.tooltip'); ?>
<?php

// case differentiation
switch ($task) {
	case "ajax":
		ajaxfields();
		break;
	case "adminview":
		showadminformChronoContact( '', $cid[0], $option );
		break;
	case "submitadminform":
		submitadminformChronoContact( $option );
		break;
	case "editdata":
		showadminformChronoContact( $cid[0], '', $option );
		break;
	case "transform":
		transformChronoContact( $cid[0], $option );
		break;
	case "savetransform":
		savetransformChronoContact( $option );
		break;
	case "previewajax":
		previewajaxChronoContact( $option );
		break;
	case 'doupgrade':
		doupgrade($option);
		break;
	case 'validatelicense':
		validatelicense($option);
		break;
	case 'validatelicenseframe':
		validatelicenseframe($option);
		break;
	case 'validatelicensedata':
		validatelicensedata();
		break;
	/////////////////////
	case "publish":
		publishChronoContact( $cid, 1, $option );
		break;
	case "unpublish":
		publishChronoContact( $cid, 0, $option );
		break;
	case "add":
		editChronoContact( 0, $option );
		break;
	case "edit":
		editChronoContact( $cid[0], $option );
		break;
	case "applychanges":
		saveChronoContact( $option, $task );
		//editChronoContact( $_POST['id'], $option );
		break;
	case "remove":
		removeChronoContact( $cid, $option );
		break;
	case "save":
		saveChronoContact( $option, $task );
		break;
	case "copy":
		copyChronoContact( $cid[0], $option );
		break;
	case "cancel":
		cancelChronoContact( $option );
		break;
	case "addmenuitem":
		addmenuitem( $option );
		break;
	////////
	case "cancelview":
		showdataChronoContact( 0, $option );
		break;
	case "show":
		$showid = (count($cid) == 0) ? 0 : $cid[0];
		showdataChronoContact( $showid, $option );
		break;
	case "viewdata":
		viewdataChronoContact( $cid[0], $option );
		break;
	case "createtable":	
		maketableChronoContact( $cid[0], $option );
		break;
	case "edittable":
		maketableChronoContact( 0, $option );
		break;
	case "tablemanager":
		tablemanagerChronoContact( $option );
		break;
	case "updatetablelist":
		updatetablelistChronoContact( $option );
		break;
	case "deletetable":
		deletetableChronoContact( $cid[0], $option);
		break;
	case "finalizetable":
		finalizetableChronoContact( $option );
		break;
	case "deleterecord":
		deleterecordChronoContact( $cid, $option );
		break;
	////// backup
	case "backup":
	case "backupall":
		backupChronoContact( $cid[0], $option, $task );
		break;
	case "restore1":
		restore1ChronoContact( 0, $option );
		break;
	case "restore2":
		restore2ChronoContact( 0, $option );
		break;
	case "backexcel":
		BackupExcel( $id, $option );
		break;
	case "backcsv":
		BackupCSV( $id, $option );
		break;
	////// config
	case 'config':
		showConfig( $option );
		break;
	case 'saveconfig':
		saveSettings( $option );
		break;
	case 'cancelconfig':
		cancelSettings( $option );
		break;
	case 'save_conf':
		save_conf( $option );
		break;
	////// wizard
	case 'form_wizard':
		form_wizard( '', $option );
		break;
	case 'wizardedit':
		form_wizard( $cid[0], $option );
		break;
	case 'wizard_elements':
		wizard_elements( $option );
		break;
	case 'save_form_wizard':
		save_form_wizard( $option );
		break;
	case 'editelement':
		editElement( $cid[0], $option );
		break;
	case 'newelement':
		editElement( 0, $option );
		break;
	case 'saveelement':
	case 'applyelement':
		saveElement( $task, $option );
		break;
	case 'cancelelement':
		cancelElement( $option );
		break;
	case 'deleteelement':
		deleteElement( $cid, $option );
		break;
	///// menu tools
	case 'menu_creator':
		menu_creator( $option );
		break;
	case 'menu_remover':
		menu_remover( $option );
		break;
	case 'menu_save':
		menu_save( $option );
		break;
	case 'menu_delete':
		menu_delete( $cid, $option );
		break;
	///////////////
	default: 
		global $mainframe;
		$database =& JFactory::getDBO();
		$switch = 1;
		if(strpos("x".$task,"plugin_")){
			$directory = JPATH_SITE.'/components/com_chronocontact/plugins/';
			$results = array();
			$handler = opendir($directory);
			while ($file = readdir($handler)) {
				if ( $file != '.' && $file != '..' && substr($file, -4) == '.php' && substr($file, 0, 3) == 'cf_')
					$results[] = str_replace(".php","", $file);
			}
			closedir($handler);
			foreach($results as $result){
				if($task == 'plugin_'.$result){
					require_once(JPATH_SITE."/components/com_chronocontact/plugins/".$result.".php");
					${$result} = new $result();
					$switch = 0;
					
					$database->setQuery( "SELECT id FROM #__chrono_contact_plugins WHERE form_id='".$cid[0]."' AND name='".$result."'" );
					$id = $database->loadResult();
					$row =& JTable::getInstance('chronocontactplugins', 'Table');
					$row->load( $id );
					${$result}->show_conf($row, $id, $cid[0], $option);
					break;
				}
			}
		}
		//echo 'xxx'.$cf_joomla_registration->result_TITLE;
		if($switch == 1){
			showChronoContact( $option );
		}
		break;
}

function save_conf( $option ){
	$plugin = JRequest::getVar('name');
	require_once(JPATH_SITE."/components/com_chronocontact/plugins/".$plugin.".php");
	${$plugin} = new $plugin();
	${$plugin}->save_conf($option);
}
function ajaxfields(){
	global $mainframe;
	$database =& JFactory::getDBO();
	$plugin = JRequest::getVar('plugin');
	$method = JRequest::getVar('method');
	require_once(JPATH_SITE."/components".DS."com_chronocontact".DS."plugins".DS.$plugin.".php");
	${$plugin} = new $plugin();
	${$plugin}->{$method}();
}
function doupgrade($option){
	global $mainframe;
	$database =& JFactory::getDBO();
	$sql = "ALTER TABLE #__chrono_contact ADD `extravalrules` LONGTEXT NOT NULL AFTER `titlesall`";
	$database->setQuery($sql);
	if (!$database->query()) {
		echo $database->getErrorMsg();
	}
	$sql = "ALTER TABLE #__chrono_contact ADD `stylecode` LONGTEXT NOT NULL AFTER `scriptcode`";
	$database->setQuery($sql);
	if (!$database->query()) {
		echo $database->getErrorMsg();
	}
	$sql = "ALTER TABLE #__chrono_contact ADD `chronocode` LONGTEXT NOT NULL AFTER `autogenerated`";
	$database->setQuery($sql);
	if (!$database->query()) {
		echo $database->getErrorMsg();
	}
	$sql = "ALTER TABLE #__chrono_contact ADD `theme` TEXT NOT NULL AFTER `chronocode`";
	$database->setQuery($sql);
	if (!$database->query()) {
		echo $database->getErrorMsg();
	}
	$sql = "ALTER TABLE #__chrono_contact_emails
ADD COLUMN `replytoname` TEXT AFTER `dfromemail`,
ADD COLUMN `dreplytoname` TEXT AFTER `replytoname`,
ADD COLUMN `replytoemail` TEXT AFTER `dreplytoname`,
ADD COLUMN `dreplytoemail` TEXT AFTER `replytoemail`;";
	$database->setQuery($sql);
	if (!$database->query()) {
		echo $database->getErrorMsg();
	}
	$sql = "ALTER TABLE #__chrono_contact_elements
ADD COLUMN `title` VARCHAR(255) AFTER `id`,
ADD COLUMN `params` LONGTEXT AFTER `code`;";
	$database->setQuery($sql);
	if (!$database->query()) {
		echo $database->getErrorMsg();
	}
	
	// Load Demo form
	$option = 'com_chronocontact';
	$filename = 'basicDemo.cfbak';
	$path = JPATH_SITE.DS.'components'.DS.'com_chronocontact'.DS.'uploads';
	$data = file_get_contents( $path.DS.$filename );					
	$data = str_replace( '&amp;', '&', $data );
	$values = '(';
	$values2 = '(';
	
	preg_match_all('/\<++(.*?)\<endendend>/s', $data, $matches);
	$i = 0;
	foreach ( $matches[0] as $match ) {
		if($i != 0){
			$values .= ',';
			$values2 .= ',';
		}
		preg_match_all('/\<++(.*?)\++>/s', $match, $match2es);
		$fieldvalue = str_replace($match2es[0][0],'',$match);
		$match2es[0][0] = str_replace('<++-++-++','',$match2es[0][0]);
		$match2es[0][0] = str_replace('++-++-++>','',$match2es[0][0]);
		$values .= $match2es[0][0];
		if($i == 0){
			$values2 .= "''";
		}else{
			$match = str_replace('<++-++-++'.$match2es[0][0].'++-++-++>','',$match);
			$match = str_replace('<endendend>','',$match);
			$match = trim($match," \t.");
			$values2 .= "'".addslashes($match)."'";
		}
		$i++;
	}
	$values .= ')';
	$values2 .= ')';
	$database->setQuery( "INSERT INTO #__chrono_contact ".$values." VALUES ".$values2 );
	if (!$database->query()) {
		JError::raiseWarning(100, "Restoring the whole form failed Failed, error : ".$database->getErrorMsg());
		//$mainframe->redirect( "index2.php?option=$option" );
	}else{
		//$mainframe->redirect( 'index2.php?option='.$option , "Restored successfully");
	}
	$lastformid = $database->insertid();
	
	// Restore Emails
	$values = '(`';
	$values2 = '(';
	$emails_data = array();
	$emails_count = explode('<cf_email_separator>', $data);
	$fields_count_1 = explode('{cfbak_start_emails}', $emails_count[0]);
	$fields_count_2 = explode('<endendend2>', $fields_count_1[1]);
	preg_match_all('/\<2++(.*?)\<endendend2>/s', $data, $matches);
	$i = 0;
	$i_v = 0;
	$counter = 0;
	foreach ( $matches[0] as $match ) {
		preg_match_all('/\<2++(.*?)\++>/s', $match, $match2es);
		$fieldvalue = str_replace($match2es[0][0],'',$match);
		$match2es[0][0] = str_replace('<2++-++-++','',$match2es[0][0]);
		$match2es[0][0] = str_replace('++-++-++>','',$match2es[0][0]);
		if($i_v < (count($fields_count_2) - 1)){
			if($i_v != 0){$values .= '`,`';}
			$values .= $match2es[0][0];
		}
		if($i != 0){$values2 .= ',';}
		if($i == 0){
			$values2 .= "''";
		}else if($i == 1){
			$values2 .= "'".$lastformid."'";
		}else{
			$match = str_replace('<2++-++-++'.$match2es[0][0].'++-++-++>','',$match);
			$match = str_replace('<endendend2>','',$match);
			$match = trim($match," \t.");
			$values2 .= "'".addslashes($match)."'";
		}
		$counter++;
		$i++;
		$i_v++;
		
		if($counter == (count($fields_count_2) - 1)){
			$values2 .= ')';
			$emails_data[] = $values2;
			$values2 = '(';
			$counter = 0;
			$i = 0;
		}
	}
	$values .= '`)';
	foreach($emails_data as $email_data){
		$database->setQuery( "INSERT INTO #__chrono_contact_emails ".$values." VALUES ".$email_data );
		if (!$database->query()) {
			JError::raiseWarning(100, "Restoring Emails Setup Failed, error : ".$database->getErrorMsg());
			//$mainframe->redirect( "index2.php?option=$option" );
		}else{
			//$mainframe->redirect( 'index2.php?option='.$option , "Restored successfully");
		}
	}
	//$mainframe->redirect( 'index2.php?option='.$option , "Demo form loaded successfully");
	
	$mainframe->redirect( "index2.php?option=$option", 'Upgrade went successfully' );
}
function validatelicense($option){
	HTML_ChronoContact::validatelicensepage( $option );
}
function validatelicenseframe($option){
	//HTML_ChronoContact::validatelicenseframe( $option );
}
function validatelicensedata(){
	global $mainframe;
	preg_match('/http(s)*:\/\/(.*?)\//i', $mainframe->getSiteURL(), $matches);
	$database =& JFactory::getDBO();
	$query     = "SELECT * FROM `#__components` WHERE `option` = 'com_chronocontact' AND parent='0' AND admin_menu_link='option=com_chronocontact'";
	$database->setQuery( $query );
	$result = $database->loadObject();
	//$configs = JComponentHelper::getParams('com_chronocontact');
	$configs = new JParameter($result->params);
	$postfields = array();
	$postfields['license_key'] = $configs->get('licensecode', '');
	$postfields['domain_name'] = $matches[2];
	$postfields['pid'] = $_POST['pid'];
	$validstatus = false;
	if(function_exists('fsockopen')){	
		$validstatus = validationconnect('http', 'www.chronoengine.com', $port='80', $path='/index2.php?option=com_chronocontact&task=extra&chronoformname=validateLicense', $postfields);
	}
	
	if((!$validstatus)||($validstatus == 'error')||!function_exists('fsockopen')){
		if (!function_exists('curl_init')){
			$validstatus = false;
		}else{
			$fields = ''; 
			$ch = curl_init();
			//$postfields = array();
			foreach( $postfields as $key => $value ) $fields .= "$key=" . urlencode( $value ) . "&";			
			curl_setopt($ch, CURLOPT_URL, 'http://www.chronoengine.com/index2.php?option=com_chronocontact&task=extra&chronoformname=validateLicense');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " ));
			if($configs->get('curlproxy', '')){
				curl_setopt($ch, CURLOPT_PROXY, $configs->get('curlproxy'));
			}
			$output = curl_exec($ch);
			curl_close($ch);
			$validstatus = $output;
		}
	}
	//return $validstatus;
	if($validstatus == 'valid'){
		if($configs->get('licensecode', '')){
			//if(!$configs->get('licensevalid', '')){
				$query     = "SELECT * FROM `#__components` WHERE `option` = 'com_chronocontact' AND parent='0' AND admin_menu_link='option=com_chronocontact'";
				$database->setQuery( $query );
				$result = $database->loadObject();
				$newline = "\n";
				if($result){
					$newparams = 'showtipoftheday='.$configs->get('showtipoftheday', 1).$newline.'licensecode='.$configs->get('licensecode', '').$newline.'licensevalid=1';
					$database->setQuery( "UPDATE `#__components` SET params='".$newparams."' WHERE id='".$result->id."'");
					if (!$database->query()) {
						JError::raiseWarning(100, $database->getErrorMsg());
						$mainframe->redirect( "index2.php?option=com_chronocontact" );
					}
				}
			//}
		}
		$mainframe->redirect( "index2.php?option=com_chronocontact", 'Your Install was validated successfully' );
	}else if($validstatus == 'invalid'){
		$query     = "SELECT * FROM `#__components` WHERE `option` = 'com_chronocontact' AND parent='0' AND admin_menu_link='option=com_chronocontact'";
		$database->setQuery( $query );
		$result = $database->loadObject();
		$newline = "\n";
		if($result){
			$newparams = 'showtipoftheday='.$configs->get('showtipoftheday', 1).$newline.'licensecode='.$configs->get('licensecode', '').$newline.'licensevalid=0';
			$database->setQuery( "UPDATE `#__components` SET params='".$newparams."' WHERE id='".$result->id."'");
			if (!$database->query()) {
				JError::raiseWarning(100, $database->getErrorMsg());
				$mainframe->redirect( "index2.php?option=com_chronocontact" );
			}
		}
		$mainframe->redirect( "index2.php?option=com_chronocontact", 'We couldn\'t validate your key because of some wrong data used' );
	}else{
		if(trim(JRequest::getVar('instantcode'))){
			$step1 = base64_decode(trim(JRequest::getVar('instantcode')));
			$step2 = str_replace(substr(md5(str_replace('www.', '', strtolower($matches[2]))), 0, 7), '', $step1);
			$step3 = str_replace(substr(md5(str_replace('www.', '', strtolower($matches[2]))), - strlen(md5(str_replace('www.', '', strtolower($matches[2])))) + 7), '', $step2);
			$step4 = str_replace(substr($configs->get('licensecode', ''), 0, 10), '', $step3);
			$step5 = str_replace(substr($configs->get('licensecode', ''), - strlen($configs->get('licensecode', '')) + 10), '', $step4);
			//echo (int)$step5;return;
			//if((((int)$step5 + (24 * 60 * 60)) > strtotime(date('d-m-Y H:i:s')))||(((int)$step5 - (24 * 60 * 60)) < strtotime(date('d-m-Y H:i:s')))){
			if(((int)$step5 < (strtotime("now") + (24 * 60 * 60)))&&((int)$step5 > (strtotime("now") - (24 * 60 * 60)))){
				$query     = "SELECT * FROM `#__components` WHERE `option` = 'com_chronocontact' AND parent='0' AND admin_menu_link='option=com_chronocontact'";
				$database->setQuery( $query );
				$result = $database->loadObject();
				$newline = "\n";
				if($result){
					$newparams = 'showtipoftheday='.$configs->get('showtipoftheday', 1).$newline.'licensecode='.$configs->get('licensecode', '').$newline.'licensevalid=1';
					$database->setQuery( "UPDATE `#__components` SET params='".$newparams."' WHERE id='".$result->id."'");
					if (!$database->query()) {
						JError::raiseWarning(100, $database->getErrorMsg());
						$mainframe->redirect( "index2.php?option=com_chronocontact" );
					}
				}
				$mainframe->redirect( "index2.php?option=com_chronocontact", 'Your key was validated successfully' );
			}else{
				$mainframe->redirect( "index2.php?option=com_chronocontact", 'Invalid instant code' );
			}
		}else{
			$query     = "SELECT * FROM `#__components` WHERE `option` = 'com_chronocontact' AND parent='0' AND admin_menu_link='option=com_chronocontact'";
			$database->setQuery( $query );
			$result = $database->loadObject();
			$newline = "\n";
			if($result){
				$newparams = 'showtipoftheday='.$configs->get('showtipoftheday', 1).$newline.'licensecode='.$configs->get('licensecode', '').$newline.'licensevalid=0';
				$database->setQuery( "UPDATE `#__components` SET params='".$newparams."' WHERE id='".$result->id."'");
				if (!$database->query()) {
					JError::raiseWarning(100, $database->getErrorMsg());
					$mainframe->redirect( "index2.php?option=com_chronocontact" );
				}
			}
			$mainframe->redirect( "index2.php?option=com_chronocontact", 'We couldn\'t validate your key because your hosting server doesn\'t have neither the CURL library nor the fsockopen functions or they may exist but don\'t function properly, please contact your host admin to fix them or contact us <a href="http://www.chronoengine.com/contactus.html">here</a> Or at this email address : webmaster@chronoengine.com' );
		}
	}
}
function validationconnect($type, $host, $port='80', $path='/', $data=array()) {
	global $mainframe;
    $_err = 'lib sockets::'.__FUNCTION__.'(): ';
	$str = '';
	$d = array();
    //switch($type) { case 'http': $type = ''; case 'ssl': continue; default: die($_err.'bad $type'); }
	
    if(!empty($data)){
		foreach($data as $k => $v){
			$strarr[] = urlencode($k).'='.urlencode($v);
		}
	}
	$str = implode('&', $strarr);
	$result = '';
	//echo $str;
    $fp = fsockopen($host, $port, $errno, $errstr, 30);
    if(!$fp){
		//$mainframe->redirect( "index2.php?option=com_chronocontact", $_err.$errstr.$errno);
		$result = 'error';
		//die($_err.$errstr.$errno);
	}else{
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: ".strlen($str)."\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $str."\r\n\r\n");
       
        while(!feof($fp)){		
			$d[] = fgets($fp,4096);
		}
        fclose($fp);
		$result = $d[count($d) - 1];
    } return $result;
} 
// Publishing of the entries
function publishChronoContact( $cid, $publish, $option ) {
	global $mainframe;
	$database =& JFactory::getDBO();
	if (count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		JError::raiseWarning(100, 'Select a item to '.$action);
		$mainframe->redirect( "index2.php?option=$option" );
	}
    $cids = implode( ',', $cid );
	$database->setQuery( "UPDATE #__chrono_contact SET published=".$publish." WHERE id IN ($cids)");
	if (!$database->query()) {
		JError::raiseWarning(100, $database->getErrorMsg());
		$mainframe->redirect( "index2.php?option=$option" );
	}
 	if (count( $cid ) == 1) {
		$row =& JTable::getInstance('chronocontact', 'Table'); 
		$row->checkin( $cid[0] );
	}
	$mainframe->redirect( "index2.php?option=$option" );
}

function editChronoContact( $id, $option ) {
	$database =& JFactory::getDBO();
	$row =& JTable::getInstance('chronocontact', 'Table'); 
	$row->load($id);
	HTML_ChronoContact::editChronoContact( $row, $option );
}
// deletion of entries
function removeChronoContact( $cid, $option ) {
	global $mainframe;
	$database =& JFactory::getDBO();
	if (!is_array( $cid ) || count( $cid ) < 1) {
		JError::raiseWarning(100, 'Please select an entry to delete');
		$mainframe->redirect( "index2.php?option=$option" );
	}
	$cids = implode( ',', $cid );
	$database->setQuery( "DELETE FROM #__chrono_contact WHERE id IN ($cids)" );
	if (!$database->query()) {
		JError::raiseWarning(100, $database->getErrorMsg());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	$database->setQuery( "DELETE FROM #__chrono_contact_emails WHERE formid IN ($cids)" );
	if (!$database->query()) {
		JError::raiseWarning(100, $database->getErrorMsg());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	$mainframe->redirect( "index2.php?option=$option" );
}
function copyChronoContact( $id , $option ) {
	global $mainframe;
	$database =& JFactory::getDBO();
	$row =& JTable::getInstance('chronocontact', 'Table'); 
	$row->load($id);
	$row->id = '';
	if (!$row->store()) {
		JError::raiseWarning(100, $row->getError());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	//copy emails
	$database->setQuery( "SELECT * FROM #__chrono_contact_emails WHERE formid='".$id."'" );
	$emails = $database->loadAssocList();
	//print_r($emails);
	foreach($emails as $email){
		$email['emailid'] = '';
		$email['formid'] = $row->id;
		$row2 =& JTable::getInstance('chronocontactemails', 'Table');
		if (!$row2->bind( $email )) {
			JError::raiseWarning(100, $row2->getError());
			$mainframe->redirect( "index2.php?option=$option" );
		}
		if (!$row2->store()) {
			JError::raiseWarning(100, $row2->getError());
			$mainframe->redirect( "index2.php?option=$option" );
		}
	}
	//copy plugins
	$database->setQuery( "SELECT * FROM #__chrono_contact_plugins WHERE form_id='".$id."'" );
	$plugins = $database->loadAssocList();
	//print_r($emails);
	foreach($plugins as $plugin){
		$plugin['id'] = '';
		$plugin['form_id'] = $row->id;
		$row3 =& JTable::getInstance('chronocontactplugins', 'Table');
		if (!$row3->bind( $plugin )) {
			JError::raiseWarning(100, $row3->getError());
			$mainframe->redirect( "index2.php?option=$option" );
		}
		if (!$row3->store()) {
			JError::raiseWarning(100, $row3->getError());
			$mainframe->redirect( "index2.php?option=$option" );
		}
	}
	$mainframe->redirect( "index2.php?option=".$option );
}
// save entry
function saveChronoContact( $option, $task ) {
	global $mainframe;
	$database =& JFactory::getDBO();
	$row =& JTable::getInstance('chronocontact', 'Table');
	$post = JRequest::get( 'post' , JREQUEST_ALLOWRAW );
	//$post = JRequest::getVar( 'description', '', 'post','string', _J_ALLOWRAW );
	if (!$row->bind( $post )) {
		JError::raiseWarning(100, $row->getError());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	
	$params = JRequest::getVar( 'params', array(), 'post', 'array');
	if (is_array( $params )) {
		$txt = array();
		foreach ( $params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		
		$plugins 	= JRequest::getVar( 'plugins', array(), 'post', 'array');
		$mplugins_order = JRequest::getVar( 'mplugins_order', array(), 'post', 'array');
		$plugins_enable 	= JRequest::getVar( 'plugins_enable', array(), 'post', 'array');
		$pluginslist = array();
		$mplugins_orderlist = array();
		foreach($plugins_enable as $k => $plugin_enable){
			if($plugin_enable == 1){
				$pluginslist[] = $plugins[$k];
				$mplugins_orderlist[] = $mplugins_order[$k];
			}
		}
		if(is_array( $plugins )){
			$txt[] = "plugins=".implode(",", $pluginslist);
		}		
		if(is_array( $mplugins_order )){
			$txt[] = "mplugins_order=".implode(",", $mplugins_orderlist);
		}
		$tablenames 	= JRequest::getVar( 'tablenames', array(), 'post', 'array');
		if(is_array( $tablenames )){
			$txt[] = "tablenames=".implode(",",$tablenames);
		}
		$row->paramsall = implode( "\n", $txt );
	}
	
	$FieldsNamesTypes = generateFieldsNamesTypes($post["html"]);
	$row->fieldsnames = implode(",", $FieldsNamesTypes["names"]);
	$row->fieldstypes = implode(",", $FieldsNamesTypes["types"]);
	
	
	$row->dbclasses = "";
	if (is_array( $params )) {
		foreach($tablenames as $tablename){
			//Create Class
			$tables = array();
			$tables[] = $tablename;
			$result = $database->getTableFields( $tables, false );
			//print_r($result[$row->tablenames]);
			$table_fields = $result[$tablename];
			$row->dbclasses .= "<?php";	
			$row->dbclasses .= "\n";
			$row->dbclasses .= "if (!class_exists('Table".str_replace($mainframe->getCfg('dbprefix'), '', $tablename)."')) {";
			$row->dbclasses .= "\n";
			$row->dbclasses .= "class Table".str_replace($mainframe->getCfg('dbprefix'), '', $tablename)." extends JTable {";	
			$primary = 'id';
			foreach($table_fields as $table_field => $field_data){
				$row->dbclasses .= "\n";
				$row->dbclasses .= "var \$".$table_field." = null;";
				if($field_data->Key == 'PRI')$primary = $table_field;
			}
			$row->dbclasses .= "\n";
			$row->dbclasses .= "function __construct( &\$database ) {";
			$row->dbclasses .= "\n";
			$row->dbclasses .= "parent::__construct( '".$tablename."', '".$primary."', \$database );";
			$row->dbclasses .= "\n";
			$row->dbclasses .= "}";
			$row->dbclasses .= "\n";
			$row->dbclasses .= "}";
			$row->dbclasses .= "\n";
			$row->dbclasses .= "}";
			$row->dbclasses .= "\n";
			$row->dbclasses .= "?>";
			$row->dbclasses .= "\n";
		}
	}
	$row->autogenerated = "";
	
	//$tables = explode("," , $paramsvalues->tablenames);
	foreach($tablenames as $tablename){
		$row->autogenerated = $row->autogenerated.'<?php
		$MyForm =& CFChronoForm::getInstance("'.$post['name'].'");
		if($MyForm->formparams("dbconnection") == "Yes"){
			$user = JFactory::getUser();			
			$row =& JTable::getInstance("'.str_replace($mainframe->getCfg("dbprefix"), "", $tablename).'", "Table");
			srand((double)microtime()*10000);
			$inum	=	"I" . substr(base64_encode(md5(rand())), 0, 16).md5(uniqid(mt_rand(), true));
			JRequest::setVar( "recordtime", JRequest::getVar( "recordtime", date("Y-m-d")." - ".date("H:i:s"), "post", "string", "" ));
			JRequest::setVar( "ipaddress", JRequest::getVar( "ipaddress", $_SERVER["REMOTE_ADDR"], "post", "string", "" ));
			JRequest::setVar( "uid", JRequest::getVar( "uid", $inum, "post", "string", "" ));
			JRequest::setVar( "cf_user_id", JRequest::getVar( "cf_user_id", $user->id, "post", "int", "" ));
			$post = JRequest::get( "post" , JREQUEST_ALLOWRAW );			
			if (!$row->bind( $post )) {
				JError::raiseWarning(100, $row->getError());
			}				
			if (!$row->store()) {
				JError::raiseWarning(100, $row->getError());
			}
			$MyForm->tablerow["'.$tablename.'"] = $row;
		}
		?>
		';
	}
	
	
	if (!$row->store()) {
		JError::raiseWarning(100, $row->getError());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	
	//Emails
	//Delet old emails and save new clean ones
	$database->setQuery( "DELETE FROM #__chrono_contact_emails WHERE formid = '".$row->id."'" );
	if (!$database->query()) {
		JError::raiseWarning(100, $database->getErrorMsg());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	preg_match_all('/start_email{.*?}end_email/i', $post['emails_temp'], $matches);
	$emails = array();
	$template_count2 = 0;
	$emails_ids =  explode(',', str_replace('email_', '', $post['emails_temp_ids']));
	foreach ( $matches[0] as $email ) {
	$template_count = $emails_ids[$template_count2+1];
	//echo $email;return;
		$email = preg_replace('/start_email{/i', '', $email);
		$email = preg_replace('/}end_email/i', '', $email);
		$email_elements = explode('||', $email);
		//$emails[] = trim($email);
		$post2 = array();
		//$post2['emailid'] = ;
		$post2['to'] = str_replace('TO=[', '', str_replace(']', '', $email_elements[0]));
		$post2['dto'] = str_replace('DTO=[', '', str_replace(']', '', $email_elements[1]));
		$post2['subject'] = str_replace('SUBJECT=[', '', str_replace(']', '', $email_elements[2]));
		$post2['dsubject'] = str_replace('DSUBJECT=[', '', str_replace(']', '', $email_elements[3]));
		$post2['cc'] = str_replace('CC=[', '', str_replace(']', '', $email_elements[4]));
		$post2['dcc'] = str_replace('DCC=[', '', str_replace(']', '', $email_elements[5]));
		$post2['bcc'] = str_replace('BCC=[', '', str_replace(']', '', $email_elements[6]));
		$post2['dbcc'] = str_replace('DBCC=[', '', str_replace(']', '', $email_elements[7]));
		$post2['fromname'] = str_replace('FROMNAME=[', '', str_replace(']', '', $email_elements[8]));
		$post2['dfromname'] = str_replace('DFROMNAME=[', '', str_replace(']', '', $email_elements[9]));
		$post2['fromemail'] = str_replace('FROMEMAIL=[', '', str_replace(']', '', $email_elements[10]));
		$post2['dfromemail'] = str_replace('DFROMEMAIL=[', '', str_replace(']', '', $email_elements[11]));
		$post2['replytoname'] = str_replace('REPLYTONAME=[', '', str_replace(']', '', $email_elements[12]));
		$post2['dreplytoname'] = str_replace('DREPLYTONAME=[', '', str_replace(']', '', $email_elements[13]));
		$post2['replytoemail'] = str_replace('REPLYTOEMAIL=[', '', str_replace(']', '', $email_elements[14]));
		$post2['dreplytoemail'] = str_replace('DREPLYTOEMAIL=[', '', str_replace(']', '', $email_elements[15]));
		$post2['formid'] = $row->id;
		
		//$post2['params'] = $post['params_email_'.$template_count];
		$params = explode(",", $post['params_email_'.$template_count]);
		$txt = array();
		$txt[0] = "recordip=".$params[0];
		$txt[1] = "emailtype=".$params[1];
		$txt[2] = "enabled=".$params[2];
		$txt[3] = "editor=".$params[3];
		$txt[4] = "enable_attachments=".$params[4];
		$post2['params'] = implode("\n", $txt);
		$post2['template'] = trim($post['editor_email_'.$template_count]) ? trim($post['editor_email_'.$template_count]) : generateAutoEmailTemplate($post['html']);
		$template_count2++;
		$post2['enabled'] = $params[2];
		
		$row2 =& JTable::getInstance('chronocontactemails', 'Table');
		if (!$row2->bind( $post2 )) {
			JError::raiseWarning(100, $row2->getError());
			$mainframe->redirect( "index2.php?option=$option" );
		}
		if (!$row2->store()) {
			JError::raiseWarning(100, $row2->getError());
			$mainframe->redirect( "index2.php?option=$option" );
		}
	}
	
	//end Emails
	if($task != 'applychanges'){
		$mainframe->redirect( "index2.php?option=".$option );
	}else{
		editChronoContact( $row->id, $option );
	}
}
// abort the current action
function cancelChronoContact( $option ) {
	global $mainframe;
	$database =& JFactory::getDBO();
	//$row =& JTable::getInstance('chronocontact', 'Table');
	//$row->bind( $_POST );
	//$row->checkin();
	$mainframe->redirect( "index2.php?option=$option" );
}
// list entries
function showChronoContact($option) {
	global $mainframe;
	$limit = $mainframe->getUserStateFromRequest($option.'.limit', 'limit', $mainframe->getCfg('list_limit'), 'int'); 
	$limitstart = $mainframe->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
	// count entries
	$database =& JFactory::getDBO();
	$database->setQuery( "SELECT count(*) FROM #__chrono_contact" );
	$total = $database->loadResult();
	echo $database->getErrorMsg();
	jimport('joomla.html.pagination'); 		
	$pageNav = new JPagination($total, $limitstart, $limit); 
	# main database query
	$database->setQuery( "SELECT * FROM #__chrono_contact ORDER BY id LIMIT $pageNav->limitstart,$pageNav->limit" );
	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		JError::raiseWarning(100, $database->stderr());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	HTML_ChronoContact::showChronoContact( $rows, $pageNav, $option );
}

///////////////////////////////
function showdataChronoContact($id, $option) {
	global $mainframe;	
	$database =& JFactory::getDBO();
	
	if(!$id){
		if(is_array(JRequest::getVar('formid', array(0), 'post', 'array'))){
			$id_arr = JRequest::getVar('formid', array(0), 'post', 'array');
			$id = $id_arr[0];
		}else{
			$id = JRequest::getVar('formid', 0, 'post', 'int');
		}
	}
	if(!$id){
		$id = JRequest::getVar('formid', 0, 'get', 'int');
	}
	
	if($id){
		$query = "SELECT * FROM #__chrono_contact WHERE id = '$id'";
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
		$paramsvalues = new JParameter($rows[0]->paramsall);
		$formtables = explode(",", $paramsvalues->get('tablenames'));
	}
	
	if(!empty($formtables)){
		$table = $formtables[0];
	}else{
		$table = JRequest::getVar('table', '');
	}
	
	$result = $database->getTableList();
	if(!in_array($table, $result) || !$table){	
		echo "<form action=\"index2.php\" method=\"post\" name=\"adminForm\">
		Table Doesn't Exist
		<input type=\"hidden\" name=\"task\" value=\"\" />
		<input type=\"hidden\" name=\"option\" value=\"$option\" />
		</form>";
	}else{
		$limit = $mainframe->getUserStateFromRequest($option.'.limit'.$table, 'limit', $mainframe->getCfg('list_limit'), 'int'); 
		$limitstart = $mainframe->getUserStateFromRequest($option.'.limitstart'.$table, 'limitstart', 0, 'int');
		// count entries
		$database->setQuery( "SELECT count(*) FROM ".$table );
		$total = $database->loadResult();
		echo $database->getErrorMsg();
		jimport('joomla.html.pagination'); 		
		$pageNav = new JPagination($total, $limitstart, $limit);
		# main database query
		# get primary key
		$tables = array();
		$tables[] = $table;
		$result = $database->getTableFields( $tables, false );
		$table_fields = $result[$table];
		$primary = '';
		foreach($table_fields as $table_field => $field_data){
			if($field_data->Key == 'PRI')$primary = $table_field;
		}
		$order = "";
		if($primary) $order = " ORDER BY ".$primary;	
		$database->setQuery( "SELECT * FROM ".$table.$order." LIMIT $pageNav->limitstart,$pageNav->limit" );
		$rows = $database->loadObjectList();
		if ($database->getErrorNum()) {
			JError::raiseWarning(100, $database->stderr());
			$mainframe->redirect( "index2.php?option=$option" );
		}
		$formid = $id;
		HTML_ChronoContact::showdataChronoContact( $rows, $pageNav, $option, $formid, $table );
	}
}
function viewdataChronoContact( $ids, $option ) {
	global $mainframe;	
	$database =& JFactory::getDBO();
	$fids = explode("_",$ids);
	$table = JRequest::getVar('table', '', 'post', 'string', 0);
	# get primary key
	$tables = array();
	$tables[] = $table;
	$result = $database->getTableFields( $tables, false );
	$table_fields = $result[$table];
	$primary = 'cf_id';
	foreach($table_fields as $table_field => $field_data){
		if($field_data->Key == 'PRI')$primary = $table_field;
	}
	$database->setQuery( "SELECT * FROM ".$table." WHERE ".$primary."=".$fids[0] );
	$rows = $database->loadObjectList();
	$row = $rows[0];
	$tablename = $table;
	//echo "SELECT * FROM ".$table." WHERE ".$primary."=".$fids[0];
	HTML_ChronoContact::viewdataChronoContact( $row, $option, $tablename, $fids[1] );
}
function tablemanagerChronoContact( $option ){
	global $mainframe;
	$database =& JFactory::getDBO();	
	$result = $database->getTableList();
	HTML_ChronoContact::tablemanagerChronoContact($option, $result);
}
function maketableChronoContact( $id, $option ) {
	global $mainframe;
	$database =& JFactory::getDBO();
	$query     = "SELECT * FROM `#__components` WHERE `option` = 'com_chronocontact' AND parent='0' AND admin_menu_link='option=com_chronocontact'";
	$database->setQuery( $query );
	$result = $database->loadObject();
	//$configs = JComponentHelper::getParams('com_chronocontact');
	$configs = new JParameter($result->params);
	
	$table = JRequest::getVar('table');
	$result = $database->getTableList();
	if ($table) {
		$tables = array();
		$tables[] = $table;
		$result = $database->getTableFields( $tables, false );
		$table_fields = $result[$table];
		
		$row = new StdClass();
		$row->id = 0;
		HTML_ChronoContact::maketableChronoContact( $row, $option, $table, $table_fields );
	} else {
		$row =& JTable::getInstance('chronocontact', 'Table');
		$row->load( $id );
		
		$html_message = "";
		
		$multipagefields = '';
		$multipagefieldsnames = array();
		//check if form is a mother form and load childs fields
		$formparams = new JParameter($row->paramsall);
		$formplugins = explode(",", $formparams->get('plugins'));
		if(in_array('cf_multi_page', $formplugins)){
			$query = "SELECT * FROM #__chrono_contact_plugins WHERE `form_id` = '".$id."' AND name='cf_multi_page'";
			$database->setQuery( $query );
			$multipageconfig = $database->loadObject();
			$multipageparams = new JParameter($multipageconfig->params);
			$multipageforms = array();
			if(trim($multipageparams->get('formsnames'))){
				$multipageforms = explode(',', $multipageparams->get('formsnames'));
				$multipagefieldsnames = array();
				$multipagefields = array();
			}
			foreach($multipageforms as $multipageform){
				$query = "SELECT * FROM #__chrono_contact WHERE `name` = '".$multipageform."'";
				$database->setQuery( $query );
				$multipageformdata = $database->loadObject();
				$multipagefields[] = $multipageformdata->fieldsnames;
			}
			$multipagefieldsnames = explode(",", implode(",", $multipagefields));
		}
		
		$defaults = array('cf_id' => (object) array('Type' => 'INT(11)', 'Key' => 'PRI', 'Extra' => 'auto_increment'),
		 'uid' => (object) array('Type' => 'VARCHAR(255)', 'Key' => '', 'Extra' => ''),
		  'recordtime' => (object) array('Type' => 'VARCHAR(255)', 'Key' => '', 'Extra' => ''),
		   'ipaddress' => (object) array('Type' => 'VARCHAR(255)', 'Key' => '', 'Extra' => ''),
		    'cf_user_id' => (object) array('Type' => 'VARCHAR(255)', 'Key' => '', 'Extra' => ''));
		$names = array();
		if(trim($row->fieldsnames)){
			$names = explode(",", $row->fieldsnames);
		}
		$fieldstypes = explode(",", $row->fieldstypes);
		foreach($names as $name){
			$defaults[$name] = (object) array('Type' => '', 'Key' => '', 'Extra' => '');
		}
		if(count($multipagefieldsnames)){
			foreach($multipagefieldsnames as $multipagefieldsname){
				$defaults[$multipagefieldsname] = (object) array('Type' => '', 'Key' => '', 'Extra' => '');
			}
		}
		HTML_ChronoContact::maketableChronoContact( $row, $option, '', $defaults );
	}
}
function finalizetableChronoContact( $option ) {
	global $mainframe;	
	$database =& JFactory::getDBO();
	$id = JRequest::getVar('formid');
	$table = JRequest::getVar('istable_name');
	$post = JRequest::get( 'post' , JREQUEST_ALLOWRAW );
	$table_sql_arr = array();
	$primarykey = '';
		
	if($id){
		$row =& JTable::getInstance('chronocontact', 'Table');
		$row->load( $id );		
		$paramsvalues = new JParameter($row->paramsall);		
		
		if(is_array($post['fields'])){
			foreach($post['fields'] as $k => $field){
				if($field && $post['fields_enable'][$k]){
					$sqlpiece = '`'.$field.'` '.$post['fields_types'][$k].' NOT NULL';
					if(JRequest::getVar('isautoincrement') == $field){
						$sqlpiece .= " auto_increment";
					}
					if(JRequest::getVar('iskey') == $field){
						$primarykey = "PRIMARY KEY  (`".$field."`)";
					}
					$table_sql_arr[] = $sqlpiece;
				}
			}
		}
		if($primarykey){
			$table_sql_arr[] = $primarykey;
		}
		if ( count($post['fields']) > 0){
			$table_sql = "CREATE TABLE `".JRequest::getVar('istable_name')."` (";
			$table_sql .= implode(", ", $table_sql_arr);
			if ($paramsvalues->get('mysql_type', 0) == 2){
				$table_sql .= ") TYPE = MYISAM ;";
			} else{ 
				$table_sql .= ") ENGINE = MYISAM ;";
			}
		}
	}else{
		if($table){
			$result = $database->getTableList();
			$table_sql = '';
			if(in_array(JRequest::getVar('tableexists'), $result)){				
				//rename table if name was changed
				if($table != JRequest::getVar('tableexists')){
					$database->setQuery('RENAME TABLE `'.JRequest::getVar('tableexists').'`  TO `'.$table.'`');
					if (!$database->query()) {
						$mainframe->redirect( 'index2.php?option='.$option, "Error while renaming table :".$database->getErrorMsg() );
					}
				}
				$tables = array();
				$tables[] = $table;
				$result = $database->getTableFields( $tables, false );
				$table_fields = $result[$table];
				
				if(is_array($post['fields'])){
					foreach($post['fields'] as $k => $field){
						$found = false;
						$sqlpiece = '';
						if($field && $post['fields_enable'][$k]){
							foreach($table_fields as $fieldname => $fielddata){
								if($field == $fieldname){
									$found = true;
									break;
								}
							}
							if(!$found){
								$sqlpiece = 'ADD COLUMN `'.$field.'` '.$post['fields_types'][$k].' NOT NULL';
							}
							if((JRequest::getVar('isautoincrement') == $field)&&$sqlpiece){
								$sqlpiece .= " auto_increment";
							}
							if((JRequest::getVar('iskey') == $field)&&(JRequest::getVar('isoldkey') != $field)){
								$primarykey = "DROP PRIMARY KEY, ADD PRIMARY KEY  (`".$field."`)";
							}
							if($sqlpiece){
								$table_sql_arr[] = $sqlpiece;
							}
						}
						if($field && !$post['fields_enable'][$k] && in_array($field, array_keys($table_fields))){
							$table_sql_arr[] = 'DROP `'.$field.'`';
						}
					}
					if($primarykey){
						$table_sql_arr[] = $primarykey;
					}
					if(implode(", ", $table_sql_arr)){
						$table_sql = 'ALTER TABLE '.$table.' '.implode(", ", $table_sql_arr).';';
					}
				}
							
			}
		}
	}
	
	$success_message = ($id) ? "Table has been created successfully" : "Table has been updated successfully";
	if($table_sql){
		$database->setQuery( $table_sql );
		if (!$database->query()) {
			$mainframe->redirect( 'index2.php?option='.$option, "Error while creating table :".$database->getErrorMsg() );
		}else{	
			$mainframe->redirect( 'index2.php?option='.$option, $success_message );
		}
	}else{
		$mainframe->redirect( 'index2.php?option='.$option, "Your changes have been applied" );
	}
			
}
function updatetablelistChronoContact( $option ) {
	global $mainframe;	
	$database =& JFactory::getDBO();
	$post = JRequest::get( 'post' , JREQUEST_ALLOWRAW );
	
	$table_sql_arr = array();
	if(is_array($post['tables'])){
		foreach($post['tables'] as $k => $table){
			if($table && !$post['tables_enable'][$k]){
				$sqlpiece = '`'.$table.'`';
				$table_sql_arr[] = $sqlpiece;
			}
		}
	}
	
	if(count($table_sql_arr)){
		$table_sql = 'DROP TABLE IF EXISTS '.implode(", ", $table_sql_arr).';';
		$database->setQuery( $table_sql );
		if (!$database->query()) {
			$mainframe->redirect( 'index2.php?option='.$option, "Error:".$database->getErrorMsg() );
		}else{	
			$mainframe->redirect( 'index2.php?option='.$option, "Tables list updated successfully" );
		}
	}else{
		$mainframe->redirect( 'index2.php?option='.$option, "No changes have been made" );
	}
	
}


/* backup ****************************************************/
function backupChronoContact( $id, $option, $task ){
global $mainframe;	
	$database =& JFactory::getDBO();
	if($task == 'backup'){
		$database->setQuery( "SELECT * FROM #__chrono_contact WHERE id='".$id."'" );
		$rows = $database->loadObjectList();
	
		
		$tablename = $mainframe->getCfg('dbprefix')."chrono_contact";
		$tables = array( $tablename );
		$result = $database->getTableFields( $tables );
		$table_fields = array_keys($result[$tablename]);
		$string = '';
		foreach($table_fields as $table_field){
			$string .= '<++-++-++'.$table_field.'++-++-++>';
			$string .= $rows[0]->$table_field;
			$string .= '<endendend>';		
		}
		
		$database->setQuery( "SELECT * FROM #__chrono_contact_emails WHERE formid='".$id."' ORDER BY emailid" );
		$emails = $database->loadObjectList();
		$tablename = $mainframe->getCfg('dbprefix')."chrono_contact_emails";
		$tables = array( $tablename );
		$result = $database->getTableFields( $tables );
		$table_fields = array_keys($result[$tablename]);
		$string2 = '';
		foreach($emails as $email){
			foreach($table_fields as $table_field){
				$string2 .= '<2++-++-++'.$table_field.'++-++-++>';
				$string2 .= $email->$table_field;
				$string2 .= '<endendend2>';		
			}
			$string2 .= '<cf_email_separator>';
		}
		if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "Opera";
		}
		elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "IE";
		} else {
			$UserBrowser = '';
		}
		$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
		@ob_end_clean();
		ob_start();
	
		header('Content-Type: ' . $mime_type);
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	
		if ($UserBrowser == 'IE') {
			header('Content-Disposition: inline; filename="' . $rows[0]->name.'.cfbak"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		else {
			header('Content-Disposition: attachment; filename="' . $rows[0]->name.'.cfbak"');
			header('Pragma: no-cache');
		}
		print $string.'
		{cfbak_start_emails}
		'.$string2.'
		{cfbak_end_emails}';
		exit();
	
	
	}else{	
		$doc = new DomDocument('1.0', 'UTF-8');
		$root = $doc->createElement('root');
		$root = $doc->appendChild($root);
		
		$database->setQuery( "SELECT * FROM #__chrono_contact" );
		$forms = $database->loadObjectList();
		$tablename = $mainframe->getCfg('dbprefix')."chrono_contact";
		$tables = array( $tablename );
		$result = $database->getTableFields( $tables );
		$table_fields = array_keys($result[$tablename]);
		
		foreach($forms as $form){
			$occ = $doc->createElement("form_".$form->name);
			$occ = $root->appendChild($occ);
			
			foreach($table_fields as $table_field){
				$child = $doc->createElement($table_field);
				$child = $occ->appendChild($child);
				$value = $doc->createTextNode($form->$table_field);
				$value = $child->appendChild($value);
			}
			//list emails
			$database->setQuery( "SELECT * FROM #__chrono_contact_emails WHERE formid='".$form->id."' ORDER BY emailid" );
			$emails = $database->loadObjectList();
			$tablename_email = $mainframe->getCfg('dbprefix')."chrono_contact_emails";
			$tables_email = array( $tablename_email );
			$result_email = $database->getTableFields( $tables_email );
			$table_fields_email = array_keys($result_email[$tablename_email]);
			
			$child = $doc->createElement("emails");
			$child = $occ->appendChild($child);
			foreach($emails as $email){
				$occ_email = $doc->createElement("email".$email->emailid);
				$occ_email = $child->appendChild($occ_email);
				
				foreach($table_fields_email as $table_field_email){
					$child_email = $doc->createElement($table_field_email);
					$child_email = $occ_email->appendChild($child_email);
					$value_email = $doc->createTextNode($email->$table_field_email);
					$value_email = $child_email->appendChild($value_email);
				}
			}
			//list plugins
			$database->setQuery( "SELECT * FROM #__chrono_contact_plugins WHERE id='".$form->id."' ORDER BY id" );
			$plugins = $database->loadObjectList();
			$tablename_plugin = $mainframe->getCfg('dbprefix')."chrono_contact_plugins";
			$tables_plugin = array( $tablename_plugin );
			$result_plugin = $database->getTableFields( $tables_plugin );
			$table_fields_plugin = array_keys($result_plugin[$tablename_plugin]);
			
			$child = $doc->createElement("plugins");
			$child = $occ->appendChild($child);
			foreach($plugins as $plugin){
				$occ_plugin = $doc->createElement($plugin->name);
				$occ_plugin = $child->appendChild($occ_plugin);
				
				foreach($table_fields_plugin as $table_field_plugin){
					$child_plugin = $doc->createElement($table_field_plugin);
					$child_plugin = $occ_plugin->appendChild($child_plugin);
					$value_plugin = $doc->createTextNode($plugin->$table_field_plugin);
					$value_plugin = $child_plugin->appendChild($value_plugin);
				}
			}
		}
		
		$xml_string = $doc->saveXML();
		
		if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "Opera";
		}
		elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "IE";
		} else {
			$UserBrowser = '';
		}
		$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
		@ob_end_clean();
		ob_start();
	
		header('Content-Type: ' . $mime_type);
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	
		if ($UserBrowser == 'IE') {
			header('Content-Disposition: inline; filename="' . "ALL_".date('d_M_Y_H:i:s').'.cfxbak"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		else {
			header('Content-Disposition: attachment; filename="' . "ALL_".date('d_M_Y_H:i:s').'.cfxbak"');
			header('Pragma: no-cache');
		}
		print $xml_string;
		exit();
	}
	
	/*if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
		$UserBrowser = "Opera";
	}
	elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
		$UserBrowser = "IE";
	} else {
		$UserBrowser = '';
	}
	$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
	@ob_end_clean();
	ob_start();

	header('Content-Type: ' . $mime_type);
	header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');

	if ($UserBrowser == 'IE') {
		header('Content-Disposition: inline; filename="' . $rows[0]->name.'.cfbak"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
	}
	else {
		header('Content-Disposition: attachment; filename="' . $rows[0]->name.'.cfbak"');
		header('Pragma: no-cache');
	}
	print $string.'
	{cfbak_start_emails}
	'.$string2.'
	{cfbak_end_emails}';
	exit();
	*/
}
function restore1ChronoContact( $id, $option ){
	HTML_ChronoContact::restoreChronoContact( $id, $option );
}
function restore2ChronoContact( $id, $option ){
global $mainframe;	
	$database =& JFactory::getDBO();
	jimport('joomla.utilities.error');
	jimport('joomla.filesystem.file');
	$id = JRequest::getVar('formid');
	//echo $_FILES['file']['type'];
	if(is_array(JRequest::getVar( 'file', '', 'files', 'array' ))){
		$file = JRequest::getVar( 'file', '', 'files', 'array' );
		$filename = $file['name'];
		$exten = explode(".",$filename);
		if($exten[count($exten)-1] == 'cfbak'){
		//if($_FILES['file']['type'] == "application/octet-stream"){
			//$filename = $_FILES['file']['name'];
			
			$path = JPATH_BASE.DS.'cache';
			$uploadedfile = JFile::upload($file['tmp_name'], $path.DS.$filename);
			//if( is_writable($path) ) {	
				if(!$uploadedfile) {
					print "<font class=\"error\">"."UPLAOD FAILED".": " . $file['error'] . "</font><br>\n";
					JError::raiseWarning(100, "UPLAOD FAILED".": " . $file['error']);
					$mainframe->redirect( "index2.php?option=$option" );
				} else {
					$data = file_get_contents( $path.DS.$filename );					
					$data = str_replace( '&amp;', '&', $data );
					$values = '(';
					$values2 = '(';
					//print $data;
					preg_match_all('/\<++(.*?)\<endendend>/s', $data, $matches);
					$i = 0;
					foreach ( $matches[0] as $match ) {
						if($i != 0){$values .= ',';$values2 .= ',';}
						//echo $match.'x';
						preg_match_all('/\<++(.*?)\++>/s', $match, $match2es);
						$fieldvalue = str_replace($match2es[0][0],'',$match);
						$match2es[0][0] = str_replace('<++-++-++','',$match2es[0][0]);
						$match2es[0][0] = str_replace('++-++-++>','',$match2es[0][0]);
						$values .= $match2es[0][0];
						if($i == 0){
							$values2 .= "''";
						}else{
							$match = str_replace('<++-++-++'.$match2es[0][0].'++-++-++>','',$match);
							$match = str_replace('<endendend>','',$match);
							$match = trim($match," \t.");
							
							$values2 .= "'".addslashes($match)."'";
						}
						$i++;
					}
					$values .= ')';
					$values2 .= ')';
					$database->setQuery( "INSERT INTO #__chrono_contact ".$values." VALUES ".$values2 );
					if (!$database->query()) {
						JError::raiseWarning(100, "Restoring the whole form failed Failed, error : ".$database->getErrorMsg());
						$mainframe->redirect( "index2.php?option=$option" );
					}else{
						//$mainframe->redirect( 'index2.php?option='.$option , "Restored successfully");
					}
					$lastformid = $database->insertid();
					
					// Restore Emails
					$values = '(`';
					$values2 = '(';
					$emails_data = array();
					$emails_count = explode('<cf_email_separator>', $data);
					$fields_count_1 = explode('{cfbak_start_emails}', $emails_count[0]);
					$fields_count_2 = explode('<endendend2>', $fields_count_1[1]);
					preg_match_all('/\<2++(.*?)\<endendend2>/s', $data, $matches);
					$i = 0;
					$i_v = 0;
					$counter = 0;
					foreach ( $matches[0] as $match ) {
						preg_match_all('/\<2++(.*?)\++>/s', $match, $match2es);
						$fieldvalue = str_replace($match2es[0][0],'',$match);
						$match2es[0][0] = str_replace('<2++-++-++','',$match2es[0][0]);
						$match2es[0][0] = str_replace('++-++-++>','',$match2es[0][0]);
						if($i_v < (count($fields_count_2) - 1)){
							if($i_v != 0){$values .= '`,`';}
							$values .= $match2es[0][0];
						}
						if($i != 0){$values2 .= ',';}
						if($i == 0){
							$values2 .= "''";
						}else if($i == 1){
							$values2 .= "'".$lastformid."'";
						}else{
							$match = str_replace('<2++-++-++'.$match2es[0][0].'++-++-++>','',$match);
							$match = str_replace('<endendend2>','',$match);
							$match = trim($match," \t.");
							$values2 .= "'".addslashes($match)."'";
						}
						$counter++;
						$i++;
						$i_v++;
						
						if($counter == (count($fields_count_2) - 1)){
							$values2 .= ')';
							$emails_data[] = $values2;
							$values2 = '(';
							$counter = 0;
							$i = 0;
						}
					}
					$values .= '`)';
					foreach($emails_data as $email_data){
						$database->setQuery( "INSERT INTO #__chrono_contact_emails ".$values." VALUES ".$email_data );
						if (!$database->query()) {
							JError::raiseWarning(100, "Restoring Emails Setup Failed, error : ".$database->getErrorMsg());
							$mainframe->redirect( "index2.php?option=$option" );
						}else{
							//$mainframe->redirect( 'index2.php?option='.$option , "Restored successfully");
						}
					}
					$mainframe->redirect( 'index2.php?option='.$option , "Restored successfully");
					
				}
			//}
		}else if($exten[count($exten)-1] == 'cfxbak'){			
			$path = JPATH_BASE.DS.'cache';
			$uploadedfile = JFile::upload($file['tmp_name'], $path.DS.$filename);
			if(!$uploadedfile) {
				print "<font class=\"error\">"."UPLAOD FAILED".": " . $file['error'] . "</font><br>\n";
				JError::raiseWarning(100, "UPLAOD FAILED".": " . $file['error']);
				$mainframe->redirect( "index2.php?option=$option" );
			} else {
				$data = file_get_contents( $path.DS.$filename );					
				$data = str_replace( '&amp;', '&', $data );					
				$xml = simplexml_load_file($path.DS.$filename);
				
				
				$post = array();
				$post_email = array();
				$post_plugin = array();				
				
				foreach($xml->children() as $form){
					foreach($form->children() as $field){
						$post[$field->getName()] = (string)$field;
						if($field->getName() == 'emails'){
							if(count($field->children())){
								foreach($field->children() as $k => $email){
									foreach($email->children() as $emaildata){
										$post_email[$k][$emaildata->getName()] = (string)$emaildata;
									}
								}
							}
						}
						if($field->getName() == 'plugins'){
							if(count($field->children())){
								foreach($field->children() as $k => $plugin){
									foreach($plugin->children() as $plugindata){
										$post_plugin[$k][$plugindata->getName()] = (string)$plugindata;
									}
								}
							}
						}
					}
					
					$row =& JTable::getInstance('chronocontact', 'Table');
					
					$post['id'] = '';				
					if (!$row->bind( $post )) {
						JError::raiseWarning(100, $row->getError());
						$mainframe->redirect( "index2.php?option=$option" );
					}
					if (!$row->store()) {
						JError::raiseWarning(100, $row->getError());
						$mainframe->redirect( "index2.php?option=$option" );
					}
					foreach($post_email as $k => $email){
						$email['formid'] = $row->id;
						$email['emailid'] = '';
						$row_email =& JTable::getInstance('chronocontactemails', 'Table');
						if (!$row_email->bind( $email )) {
							JError::raiseWarning(100, $row_email->getError());
							$mainframe->redirect( "index2.php?option=$option" );
						}
						if (!$row_email->store()) {
							JError::raiseWarning(100, $row_email->getError());
							$mainframe->redirect( "index2.php?option=$option" );
						}
					}
					foreach($post_plugin as $k => $plugin){
						$plugin['form_id'] = $row->id;
						$plugin['id'] = '';
						$row_plugin =& JTable::getInstance('chronocontactplugins', 'Table');
						if (!$row_plugin->bind( $plugin )) {
							JError::raiseWarning(100, $row_plugin->getError());
							$mainframe->redirect( "index2.php?option=$option" );
						}
						if (!$row_plugin->store()) {
							JError::raiseWarning(100, $row_plugin->getError());
							$mainframe->redirect( "index2.php?option=$option" );
						}
					}
					
					$post = array();
					$post_email = array();
					$post_plugin = array();	
					
				}
				$mainframe->redirect( 'index2.php?option='.$option , "Restored all data successfully");
			}
		}else{
			echo "Sorry, But this is not a valid ChronoForms backup file, Backup files should end with .cfbak";
		}
	}
}

function BackupExcel( $id, $option ) {
	global $mainframe;
	$database =& JFactory::getDBO();

	include_once JPATH_BASE.DS.'/components/com_chronocontact/excelwriter/'."Writer.php";
	//echo $_POST['formid'];
	$formid = JRequest::getVar( 'formid', array(), 'post', 'array');
	$database->setQuery( "SELECT name FROM #__chrono_contact WHERE id='".$formid[0]."'" );
	$formname = $database->loadResult();
	
	$tablename = JRequest::getVar('table');
	$tables = array( $tablename );
 	$result = $database->getTableFields( $tables );
	$table_fields = array_keys($result[$tablename]);
	
	$database->setQuery( "SELECT * FROM ".$tablename."" );
	$datarows = $database->loadObjectList();
	
	$xls =& new Spreadsheet_Excel_Writer();
	$xls->setVersion(8); // this fixes the 255 limit issue! :)
	$xls->send("ChronoForms_".$formname."_".date("j_n_Y").".xls");
	$format =& $xls->addFormat();
	$format->setBold();
	$format->setColor("blue");
	if (strlen($formname) > 10){$formname = substr($formname,0,10);};
	$sheet =& $xls->addWorksheet($formname.' at '.date("m-d-Y"));
	$sheet->setInputEncoding('utf-8');

	$titcol = 0;
	foreach($table_fields as $table_field){
		$sheet->writeString(0, $titcol, $table_field, $format);
		$titcol++;
	}
			
			
	$datacol = 0;
	$rowcount = 1;
	foreach($datarows as $datarow){
		foreach($table_fields as $table_field){
			$sheet->writeString($rowcount, $datacol, $datarow->$table_field, 0);
			$datacol++;
		}
		$datacol = 0;
		$rowcount++;
	}
			
	$xls->close();
	exit;
	
}
function deleterecordChronoContact( $cid_del, $option ) {
	global $mainframe;
	$database =& JFactory::getDBO();
	//echo $cid_del[0];	
	$cid_del_arr = array();
	foreach($cid_del as $cid_del_1){
		$fids = explode("_",$cid_del_1);
		$cid_del_arr[] = $fids[0];
		$formid = $fids[1];
	}
	$cid_dels = implode( ',', $cid_del_arr );
	
	if (!is_array( $cid_del ) || count( $cid_del ) < 1) {
		JError::raiseWarning(100, 'Please select an entry to delete');
		showdataChronoContact( $formid, $option );
	}
	# get primary key
	$tables = array();
	$table = JRequest::getVar('table');
	$tables[] = $table;
	$result = $database->getTableFields( $tables, false );
	$table_fields = $result[$table];
	$primary = 'cf_id';
	foreach($table_fields as $table_field => $field_data){
		if($field_data->Key == 'PRI')$primary = $table_field;
	}
	$database->setQuery( "DELETE FROM ".$table." WHERE ".$primary." IN ($cid_dels)" );
	if (!$database->query()) {
		JError::raiseWarning(100, $database->getErrorMsg());
		showdataChronoContact( $formid, $option );
	}
	showdataChronoContact( $formid, $option );
	
}

function BackupCSV( $id, $option ) {
global $mainframe;
	$database =& JFactory::getDBO();

	include_once JPATH_BASE.'/components/com_chronocontact/excelwriter/'."Writer.php";
	//echo $_POST['formid'];
	$formid = JRequest::getVar( 'formid', array(), 'post', 'array');
	$database->setQuery( "SELECT name FROM #__chrono_contact WHERE id='".$formid[0]."'" );
	$formname = $database->loadResult();
	
	$tablename = JRequest::getVar('table');
	$tables = array( $tablename );
 	$result = $database->getTableFields( $tables );
	$table_fields = array_keys($result[$tablename]);
	
	$database->setQuery( "SELECT * FROM ".$tablename."" );
	$datarows = $database->loadObjectList();
	
	$titcol = 0;
	foreach($table_fields as $table_field){
		if($titcol){$csvline .=",";}
		$csvline .= $table_field;
		$titcol++;
	}
	$csvline .="\n";
			
	$datacol = 0;
	$rowcount = 1;
	foreach($datarows as $datarow){
		foreach($table_fields as $table_field){
			if($datacol){$csvline .=",";}
			$csvline .= '"'.addslashes($datarow->$table_field).'"';
			$datacol++;
		}
		$csvline .="\n";
		$datacol = 0;
		$rowcount++;
	}
	
	if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
		$UserBrowser = "Opera";
	}
	elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
		$UserBrowser = "IE";
	} else {
		$UserBrowser = '';
	}
	$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
	@ob_end_clean();
	ob_start();

	header('Content-Type: ' . $mime_type);
	header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');

	if ($UserBrowser == 'IE') {
		header('Content-Disposition: inline; filename="' . "ChronoForms - ".$formname." - ".date("j_n_Y").'.csv"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
	}
	else {
		header('Content-Disposition: attachment; filename="' . "ChronoForms - ".$formname." - ".date("j_n_Y").'.csv"');
		header('Pragma: no-cache');
	}
	print $csvline;
	exit();
	
}
function deletetableChronoContact(  $id, $option ){
global $mainframe;
	$database =& JFactory::getDBO();
	$result = $database->getTableList();
	if (!in_array($mainframe->getCfg('dbprefix')."chronoforms_".$id, $result)) {
		JError::raiseWarning(100, "There is no table for this form to delete");
		$mainframe->redirect( "index2.php?option=$option" );
	}else{
		$database->setQuery( "DROP TABLE IF EXISTS `#__chronoforms_".$id."`;" );
		if (!$database->query()) {
			JError::raiseWarning(100, $database->getErrorMsg());
			$mainframe->redirect( "index2.php?option=$option" );
		}else{
			$mainframe->redirect( 'index2.php?option='.$option , "Table Deleted successfully");
		}
	}
}
function menu_creator($option){
	HTML_ChronoContact::menu_creator( $option );
}
function menu_save($option){
	global $mainframe;
	$database =& JFactory::getDBO();
	JTable::addIncludePath(JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'database'.DS.'table');
	$row =& JTable::getInstance('component', 'JTable');
	//$row->load(1);
	$row->name = JRequest::getVar('linktext');
	$row->parent = JRequest::getVar('parent');
	$row->ordering = JRequest::getVar('ordering');
	$row->admin_menu_link = "option=com_chronocontact&task=show&formid=".JRequest::getVar('form_id');
	$row->admin_menu_img = "js/ThemeOffice/".JRequest::getVar('icon');
	$row->option = "com_chronocontact";
	$row->link = "option=com_chronocontact&task=show&formid=1";
	$row->admin_menu_alt = JRequest::getVar('linktext');
	if (!$row->store()) {
		JError::raiseWarning(100, $row->getError());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	$mainframe->redirect( 'index2.php?option='.$option , "Menu Item Created");
}

function menu_remover($option){
	HTML_ChronoContact::menu_remover( $option );
}
function menu_delete( $cid, $option ){
	global $mainframe;
	$database =& JFactory::getDBO();
	if (!is_array( $cid ) || count( $cid ) < 1) {
		JError::raiseWarning(100, 'Please select an entry to delete');
		$mainframe->redirect( "index2.php?option=$option" );
	}
	$cids = implode( ',', $cid );
	$database->setQuery( "DELETE FROM #__components WHERE id IN ($cids)" );
	if (!$database->query()) {
		JError::raiseWarning(100, $database->getErrorMsg());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	$mainframe->redirect( 'index2.php?option='.$option.'&task=menu_remover' , "Menu Item Deleted");
}



function form_wizard($id, $option){
	global $mainframe;
	$htmloutput = '';
	$database =& JFactory::getDBO();
	$database->setQuery( "SELECT * FROM #__chrono_contact WHERE id='".$id."'" );
	$row = $database->loadObject();	
	if($id){		
		if(!trim($row->chronocode)){
			$mainframe->redirect( 'index2.php?option='.$option , "Sorry but this form was not created using the wizard");
		}
		define('CPHP_EOL', "\n");
		$old_chrono_code = $row->chronocode;
		$row->chronocode = str_replace('CHRONO_CONSTANT_EOL', CPHP_EOL, $row->chronocode);
		$XHTMLELEMENTS = explode("}]".CPHP_EOL, $row->chronocode);
		$htmlcode = '';
		$htmloutput =  '';
		//load theme
		ob_start();
		require_once(JPATH_SITE.DS."administrator".DS."components".DS."com_chronocontact".DS."wizardthemes".DS.'default'.DS."elements.php");
		$theme_elements = ob_get_clean();
		
		foreach($XHTMLELEMENTS as $XHTMLELEMENT){
			if(trim($XHTMLELEMENT)){
				$element_pieces = explode(CPHP_EOL, $XHTMLELEMENT);
				$thiselement = array();
				$counter = 0;
				foreach($element_pieces as $element_piece){
					if(($element_piece != '{')&&($element_piece != '}')&&($counter > 0)){
						$linepieces = explode(" = ", $element_piece);
						if(count($linepieces) == 2){
							$thiselement[trim($linepieces[0])] = $linepieces[1];
						}else{
							$thiselement[trim($linepieces[0])] = '';
						}
					}
					$counter++;
				}
				
				//get element tag
				$element_type = '';
				if (preg_match("/\[type=\".*?\"/i", $element_pieces[0], $matches)) {
					$element_type = str_replace(array('[type="', '"'), array('',''), $element_pieces[0]);
				} else {
					continue;
				}
				//get element from theme file
				$element_theme_1 = explode("<!--start_".$element_type."-->", $theme_elements);
				$element_theme_2 = explode("<!--end_".$element_type."-->", $element_theme_1[1]);
				//fill the element theme
				foreach($thiselement as $k => $v){
					$element_theme_2[0] = str_replace('{cf_'.$k.'}', $v, $element_theme_2[0]);
				}
				if (preg_match("/<cf_theoptions>(.*?)<\/cf_theoptions>/is", $element_theme_2[0], $matches2)) {
					//echo str_replace("<", "[", $matches2[0]);//$element_type = str_replace(array('[type="', '"'), array('',''), $element_pieces[0]);
					$option_element = str_replace(array('<cf_theoptions>', '</cf_theoptions>') , array('', ''), $matches2[0]);
					$options = explode("*,*" , $thiselement['theoptions']);
					$optionsdata = '';
					$icounter = 0;
					foreach($options as $option){
						if(trim($option)){
							$optionsdata .= str_replace(array('{value}', '{title}', '{name}'), array($option, $option, str_replace(array('[',']'), array('', '') , $thiselement['name']).$icounter), $option_element);
							$optionsdata .= CPHP_EOL;
							$icounter++;
						}
					}
					$element_theme_2[0] = str_replace($matches2[0], $optionsdata, $element_theme_2[0]);
				}else if(preg_match("/<cf_thecells>(.*?)<\/cf_thecells>/is", $element_theme_2[0], $matches2)) {
					$option_element = str_replace(array('<cf_thecells>', '</cf_thecells>') , array('', ''), $matches2[0]);
					$options = explode("*,*" , $thiselement['thecells']);
					$optionsdata = '';
					$icounter = 0;
					foreach($options as $option){
						if(trim($option)){
							$optionsdata .= str_replace(array('{value}'), array($option), $option_element);
							$optionsdata .= CPHP_EOL;
							$icounter++;
						}
					}
					$element_theme_2[0] = str_replace($matches2[0], $optionsdata, $element_theme_2[0]);
				} else{
				//echo 'not found<br>';
				}
				$htmloutput .= $element_theme_2[0];
				//add the tooltips and label styles
				if(trim($thiselement['tooltiptext'])){
					$htmloutput = str_replace("{cf_tooltip}", '<a class="tooltiplink" onclick="return false;"><img height="16" border="0" width="16" alt="" class="tooltipimg" src="components/com_chronocontact/css/images/tooltip.png"/></a>
					<div class="tooltipdiv" style="display: none;">'.trim($thiselement['tooltiptext']).'</div>', $htmloutput);
				}else{
					$htmloutput = str_replace("{cf_tooltip}", "", $htmloutput);
				}
				
				if(trim($thiselement['tooltiptext'])){
					$cf_tooltip2 = explode(" :: ", trim($thiselement['tooltiptext']));
					$htmloutput = str_replace("{cf_tooltip2}", $cf_tooltip2[1], $htmloutput);
				}else{
					$htmloutput = str_replace("{cf_tooltip2}", "", $htmloutput);
				}
				
				if(($element_pieces[0] == '[type="cf_button"')){
					if(trim($thiselement['reset']) == '1'){
						$htmloutput = str_replace("{cf_resetbutton}", '<input type="reset" name="reset" value="Reset"/>', $htmloutput);
					}else{
						$htmloutput = str_replace("{cf_resetbutton}", "", $htmloutput);
					}
				}
				
				if(trim($thiselement['hidelabel']) == '1'){
					$htmloutput = str_replace("{cf_labeloptions}", ' style="display: none;"', $htmloutput);
				}else if((trim($thiselement['labelwidth']) != 'auto')&&(trim($thiselement['labelwidth']) != '0px')){
					$htmloutput = str_replace("{cf_labeloptions}", ' style="width: '.trim($thiselement['labelwidth']).';"', $htmloutput);
				}else{
					$htmloutput = str_replace("{cf_labeloptions}", "", $htmloutput);
				}
					
				if(($element_pieces[0] == '[type="cf_dropdown"')){
					if((int)$thiselement['size'] > 1){
						$htmloutput = str_replace("{cf_multiple}", 'multiple="multiple"', $htmloutput);
					}else{
						$htmloutput = str_replace("{cf_multiple}", '', $htmloutput);
					}
				}
				
				if(($element_pieces[0] == '[type="cf_placeholder"')){
					$database->setQuery( "SELECT * FROM #__chrono_contact_elements WHERE placeholder='".$thiselement['labeltext']."' ORDER BY id" );
					$placeholder = $database->loadObject();
					if($placeholder){
						$htmloutput = str_replace("{cf_element_id}", $placeholder->id, $htmloutput);
						$htmloutput = str_replace("{cf_params}", str_replace("\n", "*,*", $placeholder->params), $htmloutput);
						$wizardelementparams = explode("\n", $placeholder->params);
						$paramsatts = "";
						foreach($wizardelementparams as $wizardelementparam){
							if(trim($wizardelementparam)){
								$paramdata = explode('=', $wizardelementparam);
								$paramsatts .= $paramdata[0].'="'.$thiselement[$paramdata[0]].'" ';
							}
						}
						$htmloutput = str_replace("{cf_params_elements}", $paramsatts, $htmloutput);
					}else{
						$htmloutput = str_replace("{cf_multiple}", '', $htmloutput);
					}
				}	
										
			}
		}
	}
	HTML_ChronoContact::form_wizard( $htmloutput, $row, $option );
}

function save_form_wizard( $option ){
	$post = JRequest::get( 'post' , JREQUEST_ALLOWRAW );
	if($post['formid'])$post['id'] = $post['formid'];
	$post['html'] = $post['form_code_temp'];
	$post['name'] = $post['form_title_temp'];
	$post['redirecturl'] = $post['redirecturl'];
	$post['onsubmitcode'] = $post['onsubmitcode'];
	if(!$post['formid']){
		$params = array();
			
		$params[] = "formmethod=post";
		$params[] = "omittedfields=";
		$params[] = "LoadFiles=Yes";
		$params[] = "submissions_limit=";
		$params[] = "tablenames=";
		$params[] = "savedataorder=";
		$params[] = "handlepostedarrays=";
		$params[] = "debug=0";
		$params[] = "mysql_type=1";
		$params[] = "enmambots=No";
		$params[] = "uploads=".($post['uploadfields'] ? 'Yes' : 'No')."";
		$params[] = "uploadfields=".$post['uploadfields']."";
		$params[] = "uploadmax=";
		$params[] = "uploadmin=";
		$params[] = "uploadpath=";
		$params[] = "dvfields=";
		$params[] = "dvrecord=Record #n";
		$params[] = "imagever=No";
		$params[] = "imtype=0";
		$params[] = "captcha_dataload=0";
		$params[] = "imgver_error_msg=";
		$params[] = "validate=No";
		$params[] = "servervalidate=";
		$params[] = "validatetype=mootools";
		$params[] = "val_required=";
		$params[] = "val_validate_number=";
		$params[] = "val_validate_digits=";
		$params[] = "val_validate_alpha=";
		$params[] = "val_validate_alphanum=";
		$params[] = "val_validate_date=";
		$params[] = "val_validate_email=";
		$params[] = "val_validate_url=";
		$params[] = "val_validate_date_au=";
		$params[] = "val_validate_currency_dollar=";
		$params[] = "val_validate_selection=";
		$params[] = "val_validate_one_required=";
		$params[] = "val_validate_confirmation=";
		$params[] = "plugins=";
		$params[] = "plugins_order=1";
		$params[] = "onsubmitcode_order=2";
		$params[] = "autogenerated_order=3";
		$params[] = "mplugins_order=";
		$params[] = "dbconnection=";
		$params[] = "datefieldformat=d/m/Y";
		$params[] = "datefieldsnames=".$post['datefieldsnames'];
		$post['paramsall'] = implode("\n", $params);
	}else{
		$params = JRequest::getVar( 'params', array(), 'post', 'array');
		if (is_array( $params )) {
			$txt = array();
			if($post['uploadfields']){
				$params['uploads'] = 'Yes';
				$params['uploadfields'] = $post['uploadfields'];
				$params['LoadFiles'] = 'Yes';
			}
			foreach ( $params as $k=>$v) {
				if($k == 'datefieldsnames')$v = $post['datefieldsnames'];
				$txt[] = "$k=$v";
			}
			$post['paramsall'] = implode( "\n", $txt );
		}
	}
	
	//print_r($post);
	global $mainframe;
	$database =& JFactory::getDBO();
	$row =& JTable::getInstance('chronocontact', 'Table');
	if (!$row->bind( $post )) {
		JError::raiseWarning(100, $row->getError());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	//auto transform to default
	//defined('PHP_EOL') or define('PHP_EOL', '\r\n');
	define('CPHP_EOL', "\n");
	$old_chrono_code = $row->chronocode;
	$row->chronocode = str_replace('CHRONO_CONSTANT_EOL', CPHP_EOL, $row->chronocode);
	$XHTMLELEMENTS = explode("}]".CPHP_EOL, $row->chronocode);
	$htmlcode = '';
	$htmloutput =  '';
	$emailoutput =  '';
	//load theme
	ob_start();
	require_once(JPATH_SITE.DS."administrator".DS."components".DS."com_chronocontact".DS."themes".DS.'default'.DS."elements.php");
	$theme_elements = ob_get_clean();
	
	ob_start();
	require_once(JPATH_SITE.DS."administrator".DS."components".DS."com_chronocontact".DS."themes".DS.'default'.DS."email.php");
	$email_template = ob_get_clean();
	
	$WizardOutput = GetWizardOutput($XHTMLELEMENTS, $theme_elements, $email_template);
	$OmittedElements = $WizardOutput['omitted'];
	
	foreach($OmittedElements as $OmittedElement){
		$WizardOutput['theme'][$OmittedElement] = '';
	}
	$htmloutput = implode('', $WizardOutput['theme']);
	$emailoutput = implode('', $WizardOutput['email']);
	
	$row->html = $htmloutput;
	$row->chronocode = $old_chrono_code;
	
	$FieldsNamesTypes = generateFieldsNamesTypes($row->html);
	$row->fieldsnames = implode(",", $FieldsNamesTypes["names"]);
	$row->fieldstypes = implode(",", $FieldsNamesTypes["types"]);
	//end auto transform
	if (!$row->store()) {
		JError::raiseWarning(100, $row->getError());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	//$mainframe->redirect( "index2.php?option=$option", 'Form Saved, you can Open form to edit other settings now!' );
	//$emailsids = JRequest::getVar( 'emailsids', array(), 'post', 'array');
	if($post['formid']){
		//Delet old emails and save new clean ones
		$database->setQuery( "DELETE FROM #__chrono_contact_emails WHERE formid = '".$post['formid']."'" );
		if (!$database->query()) {
			JError::raiseWarning(100, $database->getErrorMsg());
			$mainframe->redirect( "index2.php?option=$option" );
		}
	}
	
	$emailslist = explode(",", $post['emailslist']);
	$emailslist_clean = array();
	foreach($emailslist as $emaillist){
		if(trim($emaillist)){
			$emailslist_clean[] = $emaillist;
		}
	}
	preg_match_all('/start_email{.*?}end_email/i', $post['emails_temp'], $matches);
	$emails = array();
	$template_count = 0;
	foreach ( $matches[0] as $email ) {
	//echo $email;
		$email = preg_replace('/start_email{/i', '', $email);
		$email = preg_replace('/}end_email/i', '', $email);
		$email_elements = explode('||', $email);
		//$emails[] = trim($email);
		$post2 = array();
		$post2['to'] = str_replace('TO=[', '', str_replace(']', '', $email_elements[0]));
		$post2['dto'] = str_replace('DTO=[', '', str_replace(']', '', $email_elements[1]));
		$post2['subject'] = str_replace('SUBJECT=[', '', str_replace(']', '', $email_elements[2]));
		$post2['dsubject'] = str_replace('DSUBJECT=[', '', str_replace(']', '', $email_elements[3]));
		$post2['cc'] = str_replace('CC=[', '', str_replace(']', '', $email_elements[4]));
		$post2['dcc'] = str_replace('DCC=[', '', str_replace(']', '', $email_elements[5]));
		$post2['bcc'] = str_replace('BCC=[', '', str_replace(']', '', $email_elements[6]));
		$post2['dbcc'] = str_replace('DBCC=[', '', str_replace(']', '', $email_elements[7]));
		$post2['fromname'] = str_replace('FROMNAME=[', '', str_replace(']', '', $email_elements[8]));
		$post2['dfromname'] = str_replace('DFROMNAME=[', '', str_replace(']', '', $email_elements[9]));
		$post2['fromemail'] = str_replace('FROMEMAIL=[', '', str_replace(']', '', $email_elements[10]));
		$post2['dfromemail'] = str_replace('DFROMEMAIL=[', '', str_replace(']', '', $email_elements[11]));
		$post2['replytoname'] = str_replace('REPLYTONAME=[', '', str_replace(']', '', $email_elements[12]));
		$post2['dreplytoname'] = str_replace('DREPLYTONAME=[', '', str_replace(']', '', $email_elements[13]));
		$post2['replytoemail'] = str_replace('REPLYTOEMAIL=[', '', str_replace(']', '', $email_elements[14]));
		$post2['dreplytoemail'] = str_replace('DREPLYTOEMAIL=[', '', str_replace(']', '', $email_elements[15]));
		$post2['formid'] = $row->id;
		
		//$post2['params'] = $post['params_email_'.$template_count];
		$params = explode(",", $post['params_'.$emailslist_clean[$template_count]]);
		$txt = array();
		$txt[0] = "recordip=".$params[0];
		$txt[1] = "emailtype=".$params[1];
		$txt[2] = "enabled=".$params[2];
		$txt[3] = "editor=".$params[3];
		$txt[4] = "enable_attachments=".$params[4];
		$post2['params'] = implode("\n", $txt);
		$post2['enabled'] = $params[2];
		$post2['template'] = trim($post['editor_'.$emailslist_clean[$template_count]]) ? trim($post['editor_'.$emailslist_clean[$template_count]]) : trim($emailoutput);
		$template_count++;
		
		$row2 =& JTable::getInstance('chronocontactemails', 'Table');
		if (!$row2->bind( $post2 )) {
			JError::raiseWarning(100, $row2->getError());
		}
		if (!$row2->store()) {
			JError::raiseWarning(100, $row2->getError());
		}
	}
	$mainframe->redirect( "index2.php?option=$option", 'Form Saved, you can Open form to edit other settings now!' );
	//$emails = array_unique($emails);
}

function GetWizardOutput($XHTMLELEMENTS, $theme_elements, $email_template){
	global $mainframe;
	$database =& JFactory::getDBO();
	$ClearElements = array();	
	$ClearElements_Email = array();
	$OmittedElements = array();
	foreach($XHTMLELEMENTS as $XHTMLELEMENT){
		if(trim($XHTMLELEMENT)){
			$element_pieces = explode(CPHP_EOL, $XHTMLELEMENT);
			$thiselement = array();
			$counter = 0;
			foreach($element_pieces as $element_piece){
				if(($element_piece != '{')&&($element_piece != '}')&&($counter > 0)){
					$linepieces = explode(" = ", $element_piece);
					if(count($linepieces) == 2){
						$thiselement[trim($linepieces[0])] = $linepieces[1];
					}else{
						$thiselement[trim($linepieces[0])] = '';
					}
				}
				$counter++;
			}
			
			//get element tag
			$element_type = '';
			if (preg_match("/\[type=\".*?\"/i", $element_pieces[0], $matches)) {
				$element_type = str_replace(array('[type="', '"'), array('',''), $element_pieces[0]);
			} else {
				continue;
			}
			//get element from theme file
			$element_theme_1 = explode("<!--start_".$element_type."-->", $theme_elements);
			$element_theme_2 = explode("<!--end_".$element_type."-->", $element_theme_1[1]);
			
			if($email_template){
				$element_email_1 = explode("<!--start_".$element_type."-->", $email_template);
				$element_email_2 = explode("<!--end_".$element_type."-->", $element_email_1[1]);
			}
			//fill the element theme
			foreach($thiselement as $k => $v){
				$element_theme_2[0] = str_replace('{cf_'.$k.'}', $v, $element_theme_2[0]);
			}
			
			if($email_template){
				foreach($thiselement as $k => $v){
					$element_email_2[0] = str_replace('{cf_'.$k.'}', $v, $element_email_2[0]);
					$element_email_2[0] = str_replace('{cf_cf_'.$k.'}', '{'.str_replace("[]", "", $v).'}', $element_email_2[0]);
				}
			}
			
			if (preg_match("/<cf_theoptions>(.*?)<\/cf_theoptions>/is", $element_theme_2[0], $matches2)) {
				//echo str_replace("<", "[", $matches2[0]);//$element_type = str_replace(array('[type="', '"'), array('',''), $element_pieces[0]);
				$option_element = str_replace(array('<cf_theoptions>', '</cf_theoptions>') , array('', ''), $matches2[0]);
				$theoptions = explode("*,*" , $thiselement['theoptions']);
				$optionsdata = '';
				$icounter = 0;
				foreach($theoptions as $theoption){
					if(trim($theoption)){
						$optionsdata .= str_replace(array('{value}', '{title}', '{name}'), array($theoption, $theoption, str_replace(array('[',']'), array('', '') , $thiselement['name']).$icounter), $option_element);
						$optionsdata .= CPHP_EOL;
						$icounter++;
					}
				}
				$element_theme_2[0] = str_replace($matches2[0], $optionsdata, $element_theme_2[0]);
			}else if(preg_match("/<cf_thecells>(.*?)<\/cf_thecells>/is", $element_theme_2[0], $matches2)) {
				$option_element = str_replace(array('<cf_thecells>', '</cf_thecells>') , array('', ''), $matches2[0]);
				$theoptions = explode("*,*" , $thiselement['thecells']);
				$optionsdata = '';
				$icounter = 0;
				foreach($theoptions as $theoption){
					if(trim($theoption)){
						$optionsdata .= str_replace(array('{element}'), array($ClearElements[$theoption - 1]), $option_element);
						$optionsdata .= CPHP_EOL;
						$OmittedElements[] = $theoption - 1;
						$icounter++;
					}
				}
				$element_theme_2[0] = str_replace($matches2[0], $optionsdata, $element_theme_2[0]);
			} else{
			//echo 'not found<br>';
			}
			
			//add the tooltips and label styles
			if(trim($thiselement['tooltiptext'])){
				$element_theme_2[0] = str_replace("{cf_tooltip}", '<a class="tooltiplink" onclick="return false;"><img height="16" border="0" width="16" class="tooltipimg" alt="" src="components/com_chronocontact/css/images/tooltip.png"/></a>
				<div class="tooltipdiv">'.trim($thiselement['tooltiptext']).'</div>', $element_theme_2[0]);
			}else{
				$element_theme_2[0] = str_replace("{cf_tooltip}", "", $element_theme_2[0]);
			}
			
			if(trim($thiselement['tooltiptext'])){
				$cf_tooltip2 = explode(" :: ", trim($thiselement['tooltiptext']));
				$element_theme_2[0] = str_replace("{cf_tooltip2}", $cf_tooltip2[1], $element_theme_2[0]);
			}else{
				$element_theme_2[0] = str_replace("{cf_tooltip2}", "", $element_theme_2[0]);
			}
			
			
			if(($element_pieces[0] == '[type="cf_button"')){
				if(trim($thiselement['reset']) == '1'){
					$element_theme_2[0] = str_replace("{cf_resetbutton}", '<input type="reset" name="reset" value="Reset"/>', $element_theme_2[0]);
				}else{
					$element_theme_2[0] = str_replace("{cf_resetbutton}", "", $element_theme_2[0]);
				}
			}
			
			if(($element_pieces[0] == '[type="cf_placeholder"')){
				$database->setQuery( "SELECT * FROM #__chrono_contact_elements WHERE placeholder='".$thiselement['labeltext']."' ORDER BY id" );
				$placeholder = $database->loadObject();
				if($placeholder){
					$wizardelementparams = explode("\n", $placeholder->params);
					foreach($wizardelementparams as $wizardelementparam){
						if(trim($wizardelementparam)){
							$paramdata = explode('=', $wizardelementparam);
							$placeholder->code = str_replace("{".$paramdata[0]."}", $thiselement[$paramdata[0]], $placeholder->code);
						}
					}
					$element_theme_2[0] = str_replace("{cf_codeplace}", $placeholder->code, $element_theme_2[0]);
				}else{
					$element_theme_2[0] = str_replace("{cf_codeplace}", "", $element_theme_2[0]);
				}
			}
			
			if(trim($thiselement['hidelabel']) == '1'){
				$element_theme_2[0] = str_replace("{cf_labeloptions}", ' style="display: none;"', $element_theme_2[0]);
			}else if((trim($thiselement['labelwidth']) != 'auto')&&(trim($thiselement['labelwidth']) != '0px')){
				$element_theme_2[0] = str_replace("{cf_labeloptions}", ' style="width: '.trim($thiselement['labelwidth']).';"', $element_theme_2[0]);
			}else{
				$element_theme_2[0] = str_replace("{cf_labeloptions}", ' style="width: 150px;"', $element_theme_2[0]);
			}
				
			if(($element_pieces[0] == '[type="cf_dropdown"')){
				if((int)$thiselement['size'] > 1){
					$element_theme_2[0] = str_replace("{cf_multiple}", 'multiple="multiple"', $element_theme_2[0]);
				}else{
					$element_theme_2[0] = str_replace("{cf_multiple}", '', $element_theme_2[0]);
				}
			}
			
			if(($element_pieces[0] == '[type="cf_dropdown"')){
				if($thiselement['firstoption']){
					$element_theme_2[0] = str_replace("{cf_firstoptiondata}", '<option value="">'.($thiselement['firstoptiontext'] ? $thiselement['firstoptiontext'] : 'Choose Option').'</option>', $element_theme_2[0]);
				}else{
					$element_theme_2[0] = str_replace("{cf_firstoptiondata}", '', $element_theme_2[0]);
				}
			}
			
			$ClearElements[] = $element_theme_2[0];
			if($email_template){
				$ClearElements_Email[] = $element_email_2[0];
			}
			//$htmloutput .= $element_theme_2[0];
			//$emailoutput .= $element_email_2[0];								
		}
	}
	return array('theme' => $ClearElements, 'email' => $ClearElements_Email, 'omitted' => $OmittedElements);
}

function transformChronoContact( $id, $option ){
	global $mainframe;
	$database =& JFactory::getDBO();
	$database->setQuery( "SELECT * FROM #__chrono_contact WHERE id='".$id."'" );
	$row = $database->loadObject();
	
	if(!trim($row->chronocode)){
		$mainframe->redirect( 'index2.php?option='.$option , "Sorry but this form was not created using the wizard");
	}	
	
	HTML_ChronoContact::transformChronoContact( $row, $option );
}

function previewajaxChronoContact( $option ){
	global $mainframe;
	$database =& JFactory::getDBO();
	if(trim(JRequest::getVar('formid')) == ''){
		echo 'Something went wrong, there is no form id posted!';
		return;
	}
	$database->setQuery( "SELECT * FROM #__chrono_contact WHERE id='".JRequest::getVar('formid')."'" );
	$row = $database->loadObject();
	if(trim(JRequest::getVar('theme')) == ''){
		echo 'It looks like you didn\'t select a theme!!';
		return;
	}
	?>
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" >
    <head>
    <link rel="stylesheet" href="<?php echo $mainframe->getSiteURL(); ?>administrator/components/com_chronocontact/css/calendar2.css" type="text/css" />
    <script type="text/javascript" src="<?php echo $mainframe->getSiteURL(); ?>media/system/js/mootools.js"></script>
    <script type="text/javascript" src="<?php echo $mainframe->getSiteURL(); ?>administrator/components/com_chronocontact/js/calendar2.js"></script>
        <?php
		$directory = JPATH_SITE.'/administrator/components/com_chronocontact/themes/'.trim(JRequest::getVar('theme')).'/css/';
		$results = array();
		$handler = opendir($directory);
		while ($file = readdir($handler)) {
			if ( $file != '.' && $file != '..')
				$results[] = $file;//str_replace(".php","", $file);
		}
		closedir($handler);
		$counter = 0;
		foreach($results as $result){				
		?>	
			<link href="<?php echo $mainframe->getSiteURL().'administrator/components/com_chronocontact/themes/'.trim(JRequest::getVar('theme')).'/css/'.$result; ?>" rel="stylesheet" type="text/css" />
		<?php
		$counter++;
		}
		?>
		
    <?php
	define('CPHP_EOL', "\n");
	$old_chrono_code = $row->chronocode;
	$row->chronocode = str_replace('CHRONO_CONSTANT_EOL', CPHP_EOL, $row->chronocode);
	$XHTMLELEMENTS = explode("}]".CPHP_EOL, $row->chronocode);
	//echo $XHTMLELEMENTS[0];
	$htmlcode = '';
	$htmloutput =  '';
	//load theme
	ob_start();
	require_once(JPATH_SITE.DS."administrator".DS."components".DS."com_chronocontact".DS."themes".DS.trim(JRequest::getVar('theme')).DS."elements.php");
	$theme_elements = ob_get_clean();
	
	foreach($XHTMLELEMENTS as $XHTMLELEMENT){
		if(trim($XHTMLELEMENT)){
			$element_pieces = explode(CPHP_EOL, $XHTMLELEMENT);
			$thiselement = array();
			$counter = 0;
			foreach($element_pieces as $element_piece){
				if(($element_piece != '{')&&($element_piece != '}')&&($counter > 0)){
					$linepieces = explode(" = ", $element_piece);
					if(count($linepieces) == 2){
						$thiselement[trim($linepieces[0])] = $linepieces[1];
					}else{
						$thiselement[trim($linepieces[0])] = '';
					}
				}
				$counter++;
			}	
			
			if(($element_pieces[0] == '[type="cf_datetimepicker"')){
				?>
                <script type="text/javascript">
				window.addEvent('domready', function() { 
					myCal_<?php echo $thiselement['name']; ?> = new Calendar({ <?php echo $thiselement['name']; ?>: 'd/m/Y' }, { classes: ['dashboard'] }); 
				});
				</script>
                <?php
			}
									
		}
	}
	
	$WizardOutput = GetWizardOutput($XHTMLELEMENTS, $theme_elements, '');
	$OmittedElements = $WizardOutput['omitted'];	
	foreach($OmittedElements as $OmittedElement){
		$WizardOutput['theme'][$OmittedElement] = '';
	}
	$htmloutput = implode('', $WizardOutput['theme']);
	//$emailoutput = implode('', $WizardOutput['email']);
	
	
	
	?>
    
		<link href="<?php echo $mainframe->getSiteURL().'components/com_chronocontact/css/'; ?>tooltip.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript">
			Tips.implement({
			initialize: function(elements, lasthope,options){
					this.setOptions(options);
					this.lasthope = lasthope;
					this.toolTip = new Element('div', {
						'class': 'cf_'+this.options.className + '-tip',
						'id': this.options.className + '-tip-' + this.options.elementid,
						'styles': {
							'position': 'absolute',
							'top': '0',
							'left': '0',
							'visibility': 'hidden'
						}
					}).inject(document.body);
					this.wrapper = new Element('div').inject(this.toolTip);
					$$(elements).each(this.build, this);
					if (this.options.initialize) this.options.initialize.call(this);
				},
			
				build: function(el){
					el.$tmp.myTitle = (el.href && el.getTag() == 'a') ? el.href.replace('http://', '') : (el.rel || false);
					if (el.title){
						var dual = el.title.split('::');
						if (dual.length > 1){
							el.$tmp.myTitle = dual[0].trim();
							el.$tmp.myText = dual[1].trim();
						} else {
							el.$tmp.myText = el.title;
						}
						el.removeAttribute('title');
					} else {
						var dual = this.lasthope.split('::');
						if (dual.length > 1){
							el.$tmp.myTitle = dual[0].trim();
							el.$tmp.myText = dual[1].trim();
						} else {
							el.$tmp.myText = el.title;
						}
					}
					if (el.$tmp.myTitle && el.$tmp.myTitle.length > this.options.maxTitleChars) el.$tmp.myTitle = el.$tmp.myTitle.substr(0, this.options.maxTitleChars - 1) + "&hellip;";
					el.addEvent('mouseenter', function(event){
						this.start(el);
						if (!this.options.fixed) this.locate(event);
						else this.position(el);
					}.bind(this));
					if (!this.options.fixed) el.addEvent('mousemove', this.locate.bindWithEvent(this));
					var end = this.end.bind(this);
					el.addEvent('mouseleave', end);
					el.addEvent('trash', end);
				},
				start: function(el){
					this.wrapper.empty();
					if (el.$tmp.myTitle){
						this.title = new Element('span').inject(new Element('div', {'class': 'cf_'+this.options.className + '-title'}).inject(this.wrapper)).setHTML(el.$tmp.myTitle);
					}
					if (el.$tmp.myText){
						this.text = new Element('span').inject(new Element('div', {'class': 'cf_'+this.options.className + '-text'}).inject(this.wrapper)).setHTML(el.$tmp.myText);
					}
					$clear(this.timer);
					this.timer = this.show.delay(this.options.showDelay, this);
				}
			});
			window.addEvent('domready', function() {
				$ES('.tooltipimg').each(function(ed){
					var Tips2 = new Tips(ed, $E('div.tooltipdiv', ed.getParent().getParent()).getText(), {elementid:ed.getParent().getParent().getFirst().getNext().getProperty('id')+'_s'});
				});
			});
		</script>
    </head>
    <body id="page_bg" class="color_blue bg_blue width_fmax">    
	<?php
	echo $htmloutput;
	?>
    </body>
    </html>
    <?php
	//echo $htmlcode;
	//$mainframe->close();
	//return;
}

function savetransformChronoContact( $option ){
	global $mainframe;
	$database =& JFactory::getDBO();
	if(trim(JRequest::getVar('formid')) == ''){
		$mainframe->redirect( "index2.php?option=$option", 'Something went wrong, there is no form id posted!');
	}
	$database->setQuery( "SELECT * FROM #__chrono_contact WHERE id='".JRequest::getVar('formid')."'" );
	$row = $database->loadObject();
	if(trim(JRequest::getVar('theme')) == ''){
		$mainframe->redirect( "index2.php?option=$option", 'It looks like you didn\'t select a theme!!');
	}
	
	define('CPHP_EOL', "\n");
	$old_chrono_code = $row->chronocode;
	$row->chronocode = str_replace('CHRONO_CONSTANT_EOL', CPHP_EOL, $row->chronocode);
	$XHTMLELEMENTS = explode("}]".CPHP_EOL, $row->chronocode);
	//echo $XHTMLELEMENTS[0];
	$htmlcode = '';
	$htmloutput =  '';
	//load theme
	ob_start();
	require_once(JPATH_SITE.DS."administrator".DS."components".DS."com_chronocontact".DS."themes".DS.trim(JRequest::getVar('theme')).DS."elements.php");
	$theme_elements = ob_get_clean();
	
	$WizardOutput = GetWizardOutput($XHTMLELEMENTS, $theme_elements, '');
	$OmittedElements = $WizardOutput['omitted'];
	
	foreach($OmittedElements as $OmittedElement){
		$WizardOutput['theme'][$OmittedElement] = '';
	}
	$htmloutput = implode('', $WizardOutput['theme']);
	//$emailoutput = implode('', $WizardOutput['email']);
	
	$saverow =& JTable::getInstance('chronocontact', 'Table');
	$database->setQuery( "SELECT * FROM #__chrono_contact WHERE id='".JRequest::getVar('formid')."'" );
	$post = $database->loadAssoc();
	
	if (!$saverow->bind( $post )) {
		JError::raiseWarning(100, $saverow->getError());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	$saverow->html = $htmloutput;
	$saverow->theme = trim(JRequest::getVar('theme'));
	//end auto transform
	if (!$saverow->store()) {
		JError::raiseWarning(100, $saverow->getError());
		$mainframe->redirect( "index2.php?option=$option" );
	}else{
		$mainframe->redirect( "index2.php?option=".$option, 'Form was Successfully transformed!');
	}
	/*
	$database->setQuery( "UPDATE #__chrono_contact SET html='".$htmloutput."', theme='".trim(JRequest::getVar('theme'))."' WHERE id='".JRequest::getVar('formid')."'");
	if (!$database->query()) {
		JError::raiseWarning(100, $database->getErrorMsg());
		$mainframe->redirect( "index2.php?option=".$option );
	}else{
		$mainframe->redirect( "index2.php?option=".$option, 'Form was Successfully transformed!');
	}
	*/
}

function wizard_elements( $option ){
	global $mainframe;
	$limit = JRequest::getVar('limit', $mainframe->getCfg('list_limit')); 
	$limitstart = JRequest::getVar('limitstart', 0);
	// count entries
	$database =& JFactory::getDBO();
	$database->setQuery( "SELECT count(*) FROM #__chrono_contact_elements" );
	$total = $database->loadResult();
	echo $database->getErrorMsg();
	jimport('joomla.html.pagination'); 		
	$pageNav = new JPagination($total, $limitstart, $limit); 
	# main database query
	$database->setQuery( "SELECT * FROM #__chrono_contact_elements ORDER BY id LIMIT $pageNav->limitstart,$pageNav->limit" );
	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		JError::raiseWarning(100, $database->stderr());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	HTML_ChronoContact::wizard_elements( $rows, $pageNav, $option );
}

function editElement( $id, $option ) {
	$database =& JFactory::getDBO();
	$row =& JTable::getInstance('chronocontactelements', 'Table'); 
	$row->load($id);
	HTML_ChronoContact::editElement( $row, $option );
}

function saveElement( $task, $option ){
	global $mainframe;
	$database =& JFactory::getDBO();
	$row =& JTable::getInstance('chronocontactelements', 'Table');
	$post = JRequest::get( 'post' , JREQUEST_ALLOWRAW );
	if (!$row->bind( $post )) {
		JError::raiseWarning(100, $row->getError());
		$mainframe->redirect( "index2.php?option=$option" );
	}
	$params = JRequest::getVar( 'params', array(), 'post', 'array');
	if (is_array( $params )) {
		$txt = array();
		foreach ( $params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		$row->params = implode( "\n", $txt );
	}
	if (!$row->store()) {
		JError::raiseWarning(100, $row->getError());
		$mainframe->redirect( "index2.php?option=$option&task=tabs" );
	}
	if($task == 'applyelement'){
		editElement( $row->id, $option );
	}else{
		$mainframe->redirect( "index2.php?option=$option&task=wizard_elements" );
	}
}

function cancelElement( $option ) {
	global $mainframe;
	$database =& JFactory::getDBO();
	$row =& JTable::getInstance('chronocontactelements', 'Table'); 
	$mainframe->redirect( "index2.php?option=$option&task=wizard_elements" );
}

function deleteElement( $cid, $option ) {
	global $mainframe;
	$database =& JFactory::getDBO();
	if (!is_array( $cid ) || count( $cid ) < 1) {
		JError::raiseWarning(100, 'Please select an entry to delete');
		$mainframe->redirect( "index2.php?option=$option&task=wizard_elements" );
	}
	$cids = implode( ',', $cid );
	$database->setQuery( "DELETE FROM #__chrono_contact_elements WHERE id IN ($cids)" );
	if (!$database->query()) {
		JError::raiseWarning(100, $database->getErrorMsg());
		$mainframe->redirect( "index2.php?option=$option&task=wizard_elements" );
	}
	$mainframe->redirect( "index2.php?option=$option&task=wizard_elements" );
}

function generateAutoEmailTemplate($htmlcode){
	global $mainframe;
	$database =& JFactory::getDBO();
	//ob_start();
	/*eval( "?>".$htmlcode);*/
	$html_string = $htmlcode;//ob_get_clean();
	$usednames = array();
	//end fields names
	//text fields
	$pattern_input = '/<input([^>]*?)type=("|\')(text|password|hidden|file)("|\')([^>]*?)>/is';
	$matches = array();
	preg_match_all($pattern_input, $html_string, $matches);
	foreach ( $matches[0] as $match ) {
		$pattern_name = '/name=("|\')([^(>|"|\')]*?)("|\')/i';
		preg_match($pattern_name, $match, $matches_name);
		if (trim(str_replace('[]', '', $matches_name[2]))){				
			$email_data_name = "{".str_replace('[]', '', $matches_name[2])."}";
			if(!in_array($email_data_name, $usednames)){
				$html_string = str_replace($match, $email_data_name, $html_string);
				$usednames[] = $email_data_name;
			}else{
				$html_string = str_replace($match, "", $html_string);
			}
		}else{
			$html_string = str_replace($match, "{This_element_has_no_name_attribute}", $html_string);
		}
	}
	//buttons
	$pattern_input = '/<input([^>]*?)type=("|\')(submit|button|reset|image)("|\')([^>]*?)>/is';
	$matches = array();
	preg_match_all($pattern_input, $html_string, $matches);
	foreach ( $matches[0] as $match ) {
		$pattern_name = '/name=("|\')([^(>|"|\')]*?)("|\')/i';
		preg_match($pattern_name, $match, $matches_name);
		if (trim(str_replace('[]', '', $matches_name[2]))){				
			$email_data_name = "";
			if(!in_array($email_data_name, $usednames)){
				$html_string = str_replace($match, $email_data_name, $html_string);
				$usednames[] = $email_data_name;
			}else{
				$html_string = str_replace($match, "", $html_string);
			}
		}else{
			$html_string = str_replace($match, "{This_element_has_no_name_attribute}", $html_string);
		}
	}
	//checkboxes or radios fields
	$pattern_input = '/<input([^>]*?)type=("|\')(checkbox|radio)("|\')([^>]*?)>/is';
	$matches = array();
	$check_radio_idslist = array();
	preg_match_all($pattern_input, $html_string, $matches);
	foreach ($matches[0] as $match) {
		$pattern_id = '/id=("|\')([^(>|"|\')]*?)("|\')/i';
		$pattern_name = '/name=("|\')([^(>|"|\')]*?)("|\')/i';
		preg_match($pattern_name, $match, $matches_name);
		preg_match($pattern_id, $match, $matches_id);
		if (trim(str_replace('[]', '', $matches_name[2]))){	
			$check_radio_idslist[] = $matches_id[2];		
			$email_data_name = "{".str_replace('[]', '', $matches_name[2])."}";
			if(!in_array($email_data_name, $usednames)){
				$html_string = str_replace($match, $email_data_name, $html_string);
				$usednames[] = $email_data_name;
			}else{
				$html_string = str_replace($match, "", $html_string);
			}
		}else{
			$html_string = str_replace($match, "{This_element_has_no_name_attribute}", $html_string);
		}
	}
	//radios-checks labels
	$pattern_label = '/<label([^>]*?)for=("|\')('.implode("|", $check_radio_idslist).')("|\')([^>]*?)>(.*?)<\/label>/is';
	$matches = array();
	preg_match_all($pattern_label, $html_string, $matches);
	foreach ( $matches[0] as $match ) {
		$html_string = str_replace($match, "", $html_string);
	}
	//textarea fields
	$pattern_textarea = '/<textarea([^>]*?)>(.*?)<\/textarea>/is';
	$matches = array();
	preg_match_all($pattern_textarea, $html_string, $matches);
	$namematch = '';
	foreach ( $matches[0] as $match ) {
		$pattern_name = '/name=("|\')([^(>|"|\')]*?)("|\')/i';
		preg_match($pattern_name, $match, $matches_name);
		if (trim(str_replace('[]', '', $matches_name[2]))){				
			$email_data_name = "{".str_replace('[]', '', $matches_name[2])."}";
			if(!in_array($email_data_name, $usednames)){
				$html_string = str_replace($match, $email_data_name, $html_string);
				$usednames[] = $email_data_name;
			}else{
				$html_string = str_replace($match, "", $html_string);
			}
		}else{
			$html_string = str_replace($match, "{This_element_has_no_name_attribute}", $html_string);
		}
	}
	//select boxes
	$pattern_select = '/<select(.*?)select>/is';
	$matches = array();
	preg_match_all($pattern_select, $html_string, $matches);

	foreach ($matches[0] as $match) {
		$selectmatch = $match;
		$pattern_select2 = '/<select([^>]*?)>/is';
		preg_match_all($pattern_select2, $match, $matches2);
		$pattern_name = '/name=("|\')([^(>|"|\')]*?)("|\')/i';
		preg_match($pattern_name, $matches2[0][0], $matches_name);
		if (trim(str_replace('[]', '', $matches_name[2]))){				
			$email_data_name = "{".str_replace('[]', '', $matches_name[2])."}";
			if(!in_array($email_data_name, $usednames)){
				$html_string = str_replace($match, $email_data_name, $html_string);
				$usednames[] = $email_data_name;
			}else{
				$html_string = str_replace($match, "", $html_string);
			}
		}else{
			$html_string = str_replace($match, "{This_element_has_no_name_attribute}", $html_string);
		}
	}
	return $html_string;
	
}

function generateFieldsNamesTypes($htmlcode){
	global $mainframe;
	$database =& JFactory::getDBO();
	/*ob_start();
	eval( "?>".$htmlcode);*/
	$html_string = $htmlcode;//ob_get_clean();
	$fieldsnames = array();
	$fieldstypes = array();
	//text fields
	$pattern_input = '/<input([^>]*?)type=("|\')(text|password|hidden|file|checkbox|radio)("|\')([^>]*?)>/is';
	$matches = array();
	preg_match_all($pattern_input, $html_string, $matches);
	foreach ( $matches[0] as $match ) {
		$pattern_name = '/name=("|\')([^(>|"|\')]*?)("|\')/i';
		$pattern_type = '/type=("|\')([^(>|"|\')]*?)("|\')/i';
		preg_match($pattern_name, $match, $matches_name);
		preg_match($pattern_type, $match, $matches_type);
		if (trim(str_replace('[]', '', $matches_name[2])) && !in_array(trim(str_replace('[]', '', $matches_name[2])), $fieldsnames)){				
			$fieldsnames[] = str_replace('[]', '', $matches_name[2]);
			if ($matches_type[2]){				
				$fieldstypes[] = $matches_type[2];
			}
		}		
	}
	//textarea fields
	$pattern_textarea = '/<textarea([^>]*?)>(.*?)<\/textarea>/is';
	$matches = array();
	preg_match_all($pattern_textarea, $html_string, $matches);
	foreach ( $matches[0] as $match ) {
		$pattern_name = '/name=("|\')([^(>|"|\')]*?)("|\')/i';
		preg_match($pattern_name, $match, $matches_name);
		if (trim(str_replace('[]', '', $matches_name[2])) && !in_array(trim(str_replace('[]', '', $matches_name[2])), $fieldsnames)){				
			$fieldsnames[] = str_replace('[]', '', $matches_name[2]);
			$fieldstypes[] = "textarea";			
		}
	}
	//select boxes
	$pattern_select = '/<select(.*?)select>/is';
	$matches = array();
	preg_match_all($pattern_select, $html_string, $matches);
	foreach ($matches[0] as $match) {
		$selectmatch = $match;
		$pattern_select2 = '/<select([^>]*?)>/is';
		preg_match_all($pattern_select2, $match, $matches2);
		$pattern_name = '/name=("|\')([^(>|"|\')]*?)("|\')/i';
		preg_match($pattern_name, $matches2[0][0], $matches_name);
		if (trim(str_replace('[]', '', $matches_name[2])) && !in_array(trim(str_replace('[]', '', $matches_name[2])), $fieldsnames)){				
			$fieldsnames[] = str_replace('[]', '', $matches_name[2]);
			$fieldstypes[] = "select";			
		}
	}
	
	return array("names" => $fieldsnames, "types" => $fieldstypes);
}

?>