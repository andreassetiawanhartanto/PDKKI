<?php
/** 
* CHRONOFORMS version 3.0 
* Copyright (c) 2008 Chrono_Man, ChronoEngine.com. All rights reserved.
* Author: Chrono_Man
* License : GPL
* Visit http://www.ChronoEngine.com for regular update and information.
**/
defined('_JEXEC') or die('Restricted access');
global $mainframe;
require_once( $mainframe->getPath( 'class', 'com_chronocontact' ) );

// the class name must be the same as the file name without the .php at the end
class cf_multi_page
{
    //the next 3 fields must be defined for every plugin
    var $result_TITLE = "Multi Page";
    var $result_TOOLTIP = "Create Multi page forms easily, use this plugin with a mother form which will control all other child forms which will run in a chain";
    var $plugin_name = "cf_multi_page"; // must be the same as the class name
    var $event = "ONLOADONSUBMIT"; // must be defined and in Uppercase, should be ONSUBMIT or ONLOAD or ONLOADONSUBMIT, the last one is for v3.1 RC3 and up only
    var $plugin_keys ='';

    // the next function must exist and will have the backend config code
    function show_conf($row, $id, $form_id, $option)
    {
        global $mainframe;

        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();
                // identify and initialise the parameters used in this plugin
        $params_array = array(
            'debugging' => '0',
        	'onsubmit' => 'before_email',
        	'stepscount' => '',
        	'formsnames' => '',
        	'stepsnavigation' => '0');
        $params = $helper->loadParams($row, $params_array);

        $messages[] = '$params: '.print_r($params, true);
        if ( $params->get('debugging') ) {
    	    $helper->showPluginDebugMessages($messages);
    	}
?>
<form action="index2.php" method="post" name="adminForm" id="adminForm" class="adminForm">
<?php
        echo $pane->startPane("multipage");
        echo $pane->startPanel( 'Multi Page settings', "settings" );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('Configure the plugin', '',
            true, array('colspan' => '4', 'class' => 'cf_header'));
        echo $helper->wrapTR($input);

        foreach ( range(1, 20) as $v ) {
            $option_array[$v] = JHTML::_('select.option', $v, JText::_($v));
        }
        $tooltip = "Please select the number of separate form steps.<br />
        	Don't count the last thank your page, a confimation page, or the onsubmit routine.";
        $input = $helper->createSelectTD("Number of Steps", "params[stepscount]",
            $option_array, $params->get('stepscount'), $attribs['select'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Enter the forms names for each step separated by a comma, with no spaces.<br />
        	There must be the same number of forms as the Number of steps above";
        $input = $helper->createInputTD("Step form names",
            "params[formsnames]", $params->get('formsnames'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));
		
		$tooltip = "Enter the name of the submit button which when clicked will terminate the navigation chain";
        $input = $helper->createInputTD("Finalize button name",
            "params[finalbuttonname]", $params->get('finalbuttonname'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Enable navigating between step using urls with &cfformstep=n";
        $input = $helper->createYesNoTD("Enable Steps navigation", "params[stepsnavigation]", '',
            $params->get('stepsnavigation'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Show debug information on Submit?";
        $input = $helper->createYesNoTD("Debugging", "params[debugging]", '',
            $params->get('debugging'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));
?>
</table>
<?php
            echo $pane->endPanel();
            echo $pane->startPanel( "Help", 'Legend3' );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('How to use the Multi page plugin', '',
            true, array('colspan' => '4', 'class' => 'cf_header'));
        echo $helper->wrapTR($input);
?>
    <tr>
        <td colspan='4' style='border:1px solid silver; padding:6px;'>
        <div>The plugin allows you to link several ChronoForms together into a sequence.</div>
        <ul><li>Decide on the number of steps you want in your form sequence and create a form for each step.</li>
        <li>Put the number of form steps in the Configuration tab</li>
        <li>List the names of the forms in the input box: form_1,form_2,. . .</li>
        <li>Select Steps Navigation if you want to be able to navigate directly to form steps using
        urls with the &cfformstep parameter
        e.g. <span style='color:blue;'>index.php?option=com_chronocontact&chronoformname=test_form_8&cfformstep=2</span></li>
        </ul>
        </td>
    </tr>
</table>
<?php
        echo $pane->endPanel();
        echo $pane->endPane();

        $hidden_array = array (
            'id' => $id,
            'form_id' => $form_id,
            'name' => $this->plugin_name,
            'event' => $this->event,
            'option' => $option,
            'task' => 'save_conf');
        $hidden_array['params[onsubmit]'] = 'before_email';
        echo $helper->createHiddenArray( $hidden_array );
?>
</form>
<?php
    }

    // this function must exist and may not be changed unless you need to customize something
    function save_conf( $option )
    {
        global $mainframe;

        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'
            .DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();
        $helper->save_conf($option);
    }

    function onload( $option, $row, $params, $html_string )
    {
        global $mainframe;

        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'
            .DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();

        $messages[] = '$params: '.print_r($params, true);
        //$my 	  = JFactory::getUser();

        $session  =& JFactory::getSession();
        //$formname = JRequest::getVar('chronoformname');
		if($row->form_id){
			$formname = CFChronoForm::getFormName($row->form_id);
		}else{
			$formname = JRequest::getVar('chronoformname');
		}
        $messages[] = 'formname: '.$formname;
        $MyForm   =& CFChronoForm::getInstance($formname);
        $pages    = explode(",", $params->get('formsnames'));
        $messages[] = 'Pages: '.print_r($pages, true);

        //$CF_PATH = ($mainframe->isSite()) ? JURI::Base() : $mainframe->getSiteURL();
        $current_step = 0;
        if ( $params->get('stepsnavigation') ) {
            $current_step = JRequest::getInt('cfformstep', '0');
        }
        if ( $current_step == 0 ) {
            $session->clear('chrono_formpages_data_'.$formname, md5('chrono'));
			$current_step = 1;
        }
		$posted = array();
        if ( $session->get('chrono_formpages_data_'.$formname, array(), md5('chrono')) ) {
            $posted = $session->get('chrono_formpages_data_'.$formname, array(), md5('chrono'));
        }
        $messages[] = 'Current step: '.$current_step;
		$messages[] = 'Session data: '.print_r($posted, true);
        if ( $params->get('debugging') ) {
    	    $helper->showPluginDebugMessages($messages);
    	}
        if ( $current_step && ( $current_step <= (int)$params->get('stepscount') ) ) {
            $newForm =& CFChronoForm::getInstance(trim($pages[$current_step - 1]));
            $newForm->formrow->submiturl = $newForm->getAction($MyForm->formrow->name);
            $session->set('chrono_step_'.$formname, (int)$current_step, md5('chrono'));
            $newForm->showForm($newForm->formrow->name, $posted);
        } else {
            $newForm =& CFChronoForm::getInstance(trim($pages[0]));
            $newForm->formrow->submiturl = $newForm->getAction($MyForm->formrow->name);
            $session->set('chrono_step_'.$formname, 1, md5('chrono'));
            $newForm->showForm($newForm->formrow->name, $posted);
        }
        $html_string = '';
        $MyForm->stoploading = true;

        return $html_string ;

    }

    function onsubmit( $option, $params , $row )
    {
        global $mainframe;

        //echo "XXX";
        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'
            .DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();
		if($row->form_id){
			$formname = CFChronoForm::getFormName($row->form_id);
		}else{
			$formname = JRequest::getVar('chronoformname');
		}
        $MyForm =& CFChronoForm::getInstance($formname);
		$MyUploads =& CFUploads::getInstance($MyForm->formrow->id);
		
        $session   =& JFactory::getSession();
        $pluginrow = $row;
        $newposted = JRequest::get( 'post' , JREQUEST_ALLOWRAW );
        $oldposted = array();
        if ( $session->get('chrono_formpages_data_'.$formname, array(), md5('chrono')) ) {
            $oldposted = $session->get('chrono_formpages_data_'.$formname, array(), md5('chrono'));
        }
		if ( $session->get('chrono_formpages_files_'.$formname, array(), md5('chrono')) ) {
			$MyUploads->attachments = $session->get('chrono_formpages_files_'.$formname, array(), md5('chrono'));
		}
		
        $posted = array_merge($oldposted, $newposted);
        $messages[] = 'Posted: '.print_r($posted, true);
		$messages[] = 'Files: '.print_r($MyUploads->attachments, true);

        JRequest::set($posted, 'post');
        $session->set('chrono_formpages_data_'.$formname, $posted, md5('chrono'));
        

        $pages = explode(",", $params->get('formsnames'));
        $current_step = $session->get('chrono_step_'.$formname, '', md5('chrono'));
        $messages[] = 'Current step: '.print_r($current_step, true);
        if ( $params->get('debugging') ) {
    	    $helper->showPluginDebugMessages($messages);
    	}
		
		if(JRequest::getVar($params->get('finalbuttonname'))){
			$current_step = 'end';
		}

        if ( $current_step != 'end' ) {
            if ( $current_step ) {
                $newForm =& CFChronoForm::getInstance(trim($pages[$current_step - 1]));
                $newForm->formrow->submiturl = $newForm->getAction($MyForm->formrow->name);
                $newForm->formrow->html = $newForm->formrow->html.'<input type="hidden" name="cfformstep" value="'.$current_step.'" />';
                $newForm->submitForm($newForm->formrow->name, $posted);
				
				$newUploads =& CFUploads::getInstance($newForm->formrow->id);
				$MyUploads->attachments = array_merge($MyUploads->attachments, $newUploads->attachments);
				$session->set('chrono_formpages_files_'.$formname, $MyUploads->attachments, md5('chrono'));
                //check if the previous form submission completed successfully
                if ( $newForm->submission_complete ) {
                    if ( $current_step == (int)$params->get('stepscount') ) {
                        $session->set('chrono_step_'.$formname, 'end', md5('chrono'));
                        //$MyForm->submitForm($MyForm->formrow->name);
                        return;
                    }
                    $nextForm =& CFChronoForm::getInstance(trim($pages[$current_step]));
                    $nextForm->formrow->submiturl = $nextForm->getAction($MyForm->formrow->name);
                    $nextForm->formrow->html = $nextForm->formrow->html.'<input type="hidden" name="cfformstep" value="'.$current_step.'" />';
                    $session->set('chrono_step_'.$formname, $current_step + 1, md5('chrono'));
                    $nextForm->showForm($nextForm->formrow->name, $posted);
                }
            } else {
                $newForm =& CFChronoForm::getInstance(trim($pages[0]));
                $newForm->formrow->submiturl = $newForm->getAction($MyForm->formrow->name);
                $session->set('chrono_step_'.$formname, 1, md5('chrono'));
                $newForm->showForm($newForm->formrow->name, $posted);
            }
			//exit the form routine
			$MyForm->stoprunning = true;
			return;
        }
        
	}
}
?>