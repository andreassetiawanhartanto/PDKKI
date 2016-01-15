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
class cf_confirmation_page
{
	//the next 3 fields must be defined for every plugin
	var $result_TITLE = "Confirmation Page";
	var $result_TOOLTIP = "This plugin will help you to create a confirmation page to show the user the data he/she provided before its submitted";
	var $plugin_name = "cf_confirmation_page"; // must be the same as the class name
	var $event = "ONLOADONSUBMIT"; // must be defined and in Uppercase, should be ONSUBMIT or ONLOAD or ONLOADONSUBMIT, the last one is for v3.1 RC3 and up only
	var $plugin_keys ='';
	// the next function must exist and will have the backend config code
	function show_conf($row, $id, $form_id, $option)
	{
    	global $mainframe;

    	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'.DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();

    	// identify and initialise the parameters used in this plugin
        $params_array = array(
        	'editor' => '1',
        	'buttons' => '0');
        $params = $helper->loadParams($row, $params_array);
        if ( $params->get('editor') == 'No' ) {
            $params->set('editor', '0');
        } elseif ( $params->get('editor') == 'Yes' ) {
            $params->set('editor', '1');
        }
        if ( $params->get('buttons') == 'No' ) {
            $params->set('buttons', '0');
        } elseif ($params->get('buttons') == 'Yes' ) {
            $params->set('buttons', '1');
        }
        //echo '<div>$params: '.print_r($params, true).'</div>';
    	$params = new JParameter($row->params);
?>
<form action="index2.php" method="post" name="adminForm" id="adminForm" class="adminForm">
<?php
    	echo $pane->startPane("confirmationpage");
    	echo $pane->startPanel( 'Configure Plugin', "configure" );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('Configuration', '', true, $attribs['header']);
        echo $helper->wrapTR($input);

        $tooltip = "Disable the WYSIWYG Editor for creating the confirmation page?";
        $input = $helper->createYesNoTD("Disable WYSYWYG editor?", "params[editor]", '',
            $params->get('editor'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Show 'Submit' and 'Back' buttons in the confirmation page ?";
        $input = $helper->createYesNoTD("Show buttons?", "params[buttons]", '',
            $params->get('buttons'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));
		
		$tooltip = "Enter the submit button value e.g:Submit, default is Submit if left empty!";
        $input = $helper->createInputTD("Submit button value", "params[submit_button_value]", $params->get('submit_button_value', 'Submit'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));
		
		$tooltip = "Enter the back button value e.g:Back, default is Back if left empty!";
        $input = $helper->createInputTD("Back button value", "params[back_button_value]", $params->get('back_button_value', 'Back'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $input = $helper->createHeaderTD('Confirmation page code', '', true, $attribs['header']);
        echo $helper->wrapTR($input);

    	if ( !$params->get('editor', false ) ) {
    		$editor =& JFactory::getEditor();
    		$input = $editor->display( 'extra1', $row->extra1 , '100%', '350', '75', '20', false ) ;
    		$input = $helper->wrapTD($input, array('colspan' => '4'));
    	} else {
    		$input = $helper->createTextarea('extra1',
    		    $row->extra1, array('cols' => '85', 'rows' => '20' ), false, true, array('colspan' => '4') );
    	}
    	echo $helper->wrapTR($input, array('class' => 'cf_config'));
?>
                </td>
    		</tr>
        </table>
<?php
         echo $pane->endPanel();
         echo $pane->startPanel( 'Help', 'help' );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('How to configure the Confirmation Page plugin', '',
            true, array('colspan' => '4', 'class' => 'cf_header'));
        echo $helper->wrapTR($input);
?>
    <tr>
        <td colspan='4' style='border:1px solid silver; padding:6px;'>
        <div>The Confirmation page plugin shows a page of form results
        after the form is submitted but before emails are sent or data saved.
        This gives the user a final chance to review their input.</div>
        <ul><li>There is an WYSYWG editor configured by default, you can disable this
        on the Configuration tab if you prefer to use a plain text editor.</li>
        <li>The 'buttons' option will include 'Submit' and 'Back' buttons on the
        Confirmation page. If you prefer to add your own buttons then set this option to 'No'.</li>
        <li>In the Confirmation Page Code box you may enter HTML to display the
        submitted results. Use {field_name} to show the value of any field from the form.</li>
        <li>You can also include PHP in this box if you need to process the data for display.
        PHP must be enclosed in &lt;?php . . . ?&gt; tags.</li>
        <li>If you wish to use PHP or complex HTML, you may prefer to disable the WYSYWYG editor as
        it may change some code entries.</li>
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

		$my = JFactory::getUser();
		$db =& JFactory::getDBO();

		$session      =& JFactory::getSession();
		//get chrono instances
		$formname     = JRequest::getVar( 'chronoformname');
		$MyForm       =& CFChronoForm::getInstance($formname);
		$MyFormEmails =& CFEMails::getInstance($MyForm->formrow->id);
		$MyCustomCode =& CFCustomCode::getInstance($MyForm->formrow->id);
		$MyPlugins    =& CFPlugins::getInstance($MyForm->formrow->id);

		$chrono_next_step = $session->get('chrono_next_step', '', md5('chrono'));
		if ( JRequest::getVar('task') != 'beforeshow' ) {
			$session->set("chrono_next_step", '', md5('chrono'));
		}
		

		if ( $chrono_next_step == 'confirm' ) {
			if ( (!JRequest::checkToken()) && $MyForm->formparams('checkToken', 1) ) {
				echo "You are not allowed to access this URL";
				return;
			}
			$html_string = '';
			$posted = JRequest::get( 'post' , JREQUEST_ALLOWRAW );

			if ( JRequest::getVar('confirm') == trim($params->get('submit_button_value', 'Submit')) ) {

				$debug = $MyForm->formparams('debug');
				//handle arrays
				$MyForm->handleArrays($MyForm->formrow->name);
				/**
				 * If there are no errors and e-mail is required then build and send it.
				 */
				if ( ($MyForm->formrow->emailresults != 0) && !$MyForm->error_found && !$MyForm->stoprunning ) {
					//run before submit code
					if ( !$MyForm->haltFunction["onsubmitcodeb4"] ) {
						$MyCustomCode->runCode( 'onsubmitcodeb4' );
						if ( $MyForm->showFormErrors($MyForm->formrow->name) ) {
							$MyForm->showForm($MyForm->formrow->name, $posted);
							return;
						}
					}
					if ( !$MyForm->haltFunction["autogenerated_before_email"] ) {
						$MyCustomCode->runCode( 'autogenerated', 'before_email' );
					}
					//send emails
					if ( !$MyForm->haltFunction["emails"] ) {
						$emails_result = $MyFormEmails->sendEmails($MyForm, $MyFormEmails->emails);
					}
				}

				if ( !$MyForm->error_found && !$MyForm->stoprunning ) {
				/*************** check to see if order was specified, if not then use the default old one ************************/
					if ( !$MyForm->formparams('plugins_order')
					        && !$MyForm->formparams('onsubmitcode_order')
					        && !$MyForm->formparams('autogenerated_order') ) {
						$MyForm->setFormParam('autogenerated_order', 3);
						$MyForm->setFormParam('onsubmitcode_order', 2);
						$MyForm->setFormParam('plugins_order', 1);
					}

					for ( $ixx = 1 ; $ixx <= 3; $ixx++ ) {
						if ( $MyForm->formparams('plugins_order') == $ixx ) {
							if ( !$MyForm->haltFunction["plugins_after_email"] ) {
								$MyPlugins->runPlugin('after_email');
								//show errors if any
								if ( $MyForm->showFormErrors($MyForm->formrow->name) ) {
									$MyForm->showForm($MyForm->formrow->name, $posted);
									return;
								}
							}
						}
						/**
						 * Run the On-submit 'post e-mail' code if there is any
						 */
						if ( $MyForm->formparams('onsubmitcode_order') == $ixx ) {
							if ( !$MyForm->haltFunction["onsubmitcode"] ) {
								$MyCustomCode->runCode( 'onsubmitcode' );
								if ( $MyForm->showFormErrors($MyForm->formrow->name) ) {
									$MyForm->showForm($MyForm->formrow->name, $posted);
									return;
								}
							}
						}

						/**
						 * Run the SQL query if there is one
						 */
						if ( $MyForm->formparams('autogenerated_order') == $ixx ) {
							if ( !$MyForm->haltFunction["autogenerated_after_email"] ) {
								$MyCustomCode->runCode( 'autogenerated', 'after_email' );
							}
						}
					}
					//Mark submission as complete!
					$MyForm->submission_complete = true;
					$MyForm->addDebugMsg('Debug End');
					/**
					 * Redirect the page if requested
					 */
					if ( !empty($MyForm->formrow->redirecturl) ) {
						if ( !$debug ) {
							$mainframe->redirect($MyForm->formrow->redirecturl);
						} else {
							$MyForm->addDebugMsg("<div class='debug' >Redirect link set, click to test:<br /><a href='".$MyForm->formrow->redirecturl."'>".$MyForm->formrow->redirecturl."</a></div>");
						}
					}
				}
				if ( !empty($MyForm->formdebug) && ($MyForm->formparams('debug') == '1') ) {
					include_once(JPATH_COMPONENT.DS.'libraries'.DS.'includes'.DS.'Debug.php');
				}
				$html_string = '';

			} else {
				$session->set("chrono_next_step", '', md5('chrono'));
				$MyForm->showForm($MyForm->formrow->name, $posted);
				$MyForm->stoploading = true;
				return;
				$html_string = '';
			}
		}

		return $html_string ;

	}

	function onsubmit( $option, $params , $row )
	{
		global $mainframe;

		$db        =& JFactory::getDBO();
		$pluginrow = $row;

		$formname  = JRequest::getVar( 'chronoformname');
		$MyForm    =& CFChronoForm::getInstance($formname);

        $posted    = JRequest::get( 'post' , JREQUEST_ALLOWRAW );
		$session   =& JFactory::getSession();
		$session->set("chrono_next_step", 'confirm', md5('chrono'));

		//show the form
		if ( !empty($MyForm->formrow->submiturl) ) {
			$actionurl = $MyForm->formrow->submiturl;
		} else {
			$actionurl = JURI::Base().'index.php?option=com_chronocontact&amp;task=beforeshow&amp;chronoformname='.$MyForm->formrow->name;
			if ( JRequest::getInt('Itemid') ) {
				$actionurl = $actionurl.'&amp;Itemid='.JRequest::getInt('Itemid');
			}
		}
		$multipart = "";
		if ( $MyForm->formparams('uploads') == 'Yes' ) {
		        $multipart = ' enctype="multipart/form-data"';
		}
        echo "<form name='ChronoContact_".$MyForm->formrow->name."'
            id='ChronoContact_".$MyForm->formrow->name."'
            method='".$MyForm->formparams('formmethod')."'
            $multipart
            action='$actionurl' ".$MyForm->formrow->attformtag." >";

		//run the confirmation page code
		if ( !empty($pluginrow->extra1) ) {
			ob_start();
			eval( "?>".$pluginrow->extra1 );
			$extra1 = ob_get_clean();
			foreach ( $posted as $name => $post) {
				if ( is_array($post)) {
					$post = implode(", ", $post);
				}
				$extra1 = str_replace("{".$name."}", $post, $extra1);
				echo '<input type="hidden" name="'.$name.'" value="'.$post.'" />
				';
			}
			echo $extra1;
		}
		if ( $params->get('buttons') == '1' ) {
?>
            <div class="form_element cf_button">
            	<input type="submit" name="confirm" value="<?php echo $params->get('submit_button_value'); ?>"/>
                <input type="submit" name="confirm" value="<?php echo $params->get('back_button_value'); ?>"/>
            </div>
<?php
		}
		echo JHTML::_( 'form.token' );
?>
		</form>
<?php
		//exit the form routine
		$MyForm->stoprunning = true;
		return;
	}
}
?>