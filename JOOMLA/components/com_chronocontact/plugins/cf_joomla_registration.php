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
class cf_joomla_registration
{
    //the next 3 fields must be defined for every plugin
    var $result_TITLE = "Joomla Registration";
    var $result_TOOLTIP = "Synchronize the submitted data with this form with the default Joomla users table";
    var $plugin_name = "cf_joomla_registration"; // must be the same as the class name
    var $event = "ONLOADONSUBMIT"; // must be defined and in Uppercase, should be ONSUBMIT or ONLOAD
    var $plugin_keys ='errors,user,complete';//you can use these keys later here to hold some values in the MyPlugins->plugin_name variable array!
    var $params = null;

    // the next function must exist and will have the backend config code
    function show_conf($row, $id, $form_id, $option)
    {
        global $mainframe;

        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();
                // identify and initialise the parameters used in this plugin
        $params_array = array(
            'debugging' => '0',
        	'name' => '',
        	'username' => '',
        	'email' => '',
            'pass' => '',
            'vpass' => '',
            'emailuser' => 'Yes',
            'emailadmins' => 'Yes',
            'joomlastatus' => '1',
            'showmessages' => '1',
            'createpassword' => '0',
            'onsubmit' => 'before_email',
			'new_usertype' => 'Registered',
            'autologin' => '0');
        $params = $helper->loadParams($row, $params_array);

        $messages[] = '$params: '.print_r($params, true);
        if ( $params->get('debugging') ) {
    	    $helper->showPluginDebugMessages($messages);
    	}
?>
<form action="index2.php" method="post" name="adminForm" id="adminForm" class="adminForm">
<?php
        echo $pane->startPane("joomlaregistration");
        echo $pane->startPanel( 'Field names', "general6" );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('User information field names', '', true, $attribs['header']);
        echo $helper->wrapTR($input);

        $tooltip = "Enter the name of the the 'User's name' field";
        $input = $helper->createInputTD("'Name' field name",
            "params[name]", $params->get('name'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Enter the name of the the 'User's username' field";
        $input = $helper->createInputTD("'Username' field name",
            "params[username]", $params->get('username'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Enter the name of the the 'User's email' field";
        $input = $helper->createInputTD("'Email' field name",
            "params[email]", $params->get('email'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Enter the name of the the 'User's password' field";
        $input = $helper->createInputTD("'Password' field name",
            "params[pass]", $params->get('pass'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Enter the name of the the 'Confirm password' field";
        $input = $helper->createInputTD("'Confirm password' field name",
            "params[vpass]", $params->get('vpass'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        echo "</table>";
        echo $pane->endPanel();
        echo $pane->startPanel( 'Configuration', "general6" );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('Registration configuration', '', true, $attribs['header']);
        echo $helper->wrapTR($input);

        $option_array = array('Yes' => 'Yes (Default Joomla Email)', 'No' => 'No' );
                $query = "
        	SELECT *
        		FROM `#__chrono_contact_emails`
        		WHERE `formid` = '$form_id'
        		ORDER BY emailid ;
        	";
        $db->setQuery( $query );
        $emails = $db->loadObjectList();
        $emailcount = 1;
        foreach ( $emails as $email ) {
            $option_array['custom'.$emailcount] =  "Yes (Custom Form Email $emailcount)";
            $emailcount++;
        }
        foreach ( $option_array as $k => $v ) {
            $option_array[$k] = JHTML::_('select.option', $k, JText::_($v));
        }
        $tooltip = "Select an e-mail template to send to the User";
        $input = $helper->createSelectTD("E-mail the User?", "params[emailuser]",
            $option_array, $params->get('emailuser'), $attribs['select'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Select an e-mail template to send to the Admins";
        $input = $helper->createSelectTD("E-mail the Admins?", "params[emailadmins]",
            $option_array, $params->get('emailadmins'), $attribs['select'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Show Joomla status messages<br />
        	e.g: Your account has been created . . .";
        $input = $helper->createYesNoTD("Show Joomla messages", "params[joomlastatus]", '',
            $params->get('joomlastatus'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Show messages using the Joomla message display.<br />
        	These may not show if your template doesn't support system message display.";
        $input = $helper->createYesNoTD("Use Joomla message display", "params[showmessages]", '',
            $params->get('showmessages'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Automatically create a password for the user.<br />
        	Note: Any password fields on the form will be ignored.";
        $input = $helper->createYesNoTD("Create password", "params[createpassword]", '',
            $params->get('createpassword'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));
		
		$tooltip = "you may want the plugin to advance with the registration even if Joomla global allow user registration is off";
        $input = $helper->createYesNoTD("Override Joomla's Allow User Registration", "params[overrideJallowUserRegistration]", '',
            $params->get('overrideJallowUserRegistration', '0'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Auto login after registration, will work only if user is activated";
        $input = $helper->createYesNoTD("Auto login", "params[autologin]", '',
            $params->get('autologin'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $option_array = array('before_email' => 'Before Email', 'after_email' => 'After Email' );
        foreach ( $option_array as $k => $v ) {
            $option_array[$k] = JHTML::_('select.option', $k, JText::_($v));
        }
        $tooltip = "Run the plugin before or after the email.<br />
        	Running it before the email may be necessary to include some data into the email";
        $input = $helper->createSelectTD("Flow control", "params[onsubmit]",
            $option_array, $params->get('onsubmit'), $attribs['select'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Show debug information on Submit?";
        $input = $helper->createYesNoTD("Debugging", "params[debugging]", '',
            $params->get('debugging'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));
		
		$option_array = array('Registered' => 'Registered', 'Author' => 'Author', 'Editor' => 'Editor', 'Publisher' => 'Publisher', 'Manager' => 'Manager', 'Administrator' => 'Administrator', 'Super Administrator' => 'Super Administrator');
        foreach ( $option_array as $k => $v ) {
            $option_array[$k] = JHTML::_('select.option', $k, JText::_($v));
        }
        $tooltip = "Select to which users group the new user will be added";
        $input = $helper->createSelectTD("Target group", "params[new_usertype]",
            $option_array, $params->get('new_usertype', 'Registered'), $attribs['select'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

 ?>
</table>
<?php
        echo $pane->endPanel();
        echo $pane->startPanel( "Extra code", 'extracode' );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('Extra code', '',
            true, array('colspan' => '4', 'class' => 'cf_header'));
        echo $helper->wrapTR($input);

        $tooltip = "Execute some code just before the Registration transaction is executed";
        $input = $helper->createTextareaTD('Extra before Registration code', 'extra4',
            $row->extra4, $attribs['textarea'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Execute some code just after the Registration transaction is executed";
        $input = $helper->createTextareaTD('Extra after Registration code', 'extra5',
            $row->extra5, $attribs['textarea'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));
 ?>
</table>
<?php
            echo $pane->endPanel();
            echo $pane->startPanel( "Help", 'Legend3' );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('How to configure the Jooma Registration plugin', '',
            true, array('colspan' => '4', 'class' => 'cf_header'));
        echo $helper->wrapTR($input);
?>
    <tr>
        <td colspan='4' style='border:1px solid silver; padding:6px;'>
        <div>The plugin allows you to use a ChronoForms Form to register users with your Joomla site.
        The form will be redisplayed if there is a registration error (the password confirmation does not match,
        the username or email are already in use, email is invalid, etc.). The password field values are not re-shown for security.</div>
        <ul><li>On the 'Field Names' tab enter the field names from your form that correspond
        to the standard Joomla Registration fields. If here are fields that you are not using leave them blank.</li>
        <li>On the Configuration tab you can choose the emails to send to the User or the Admins.
        If you have Custom Email Setups for this form they will also show in the lists.</li>
        <li>The Flow Control setting should normally be left as 'Before Email'
        (but note that emails must be enabled on your form General Tab or the plugin will not run).</li>
        <li>Debugging will show some extra messages that may be helpful in setting up the plugin.
        This should be set to 'No' on a production site.</li>
        <li>The Create Password option will use the standard Joomla function to create a random string password.
            This can simplify the registration process and remove a security risk if users tend to use weak passwords.
            If you select this option you do not need password fields in your form and any that you do have will be ignored.</li>
        <li>The Use Joomla Message Display options will show standard Joomla system messages if there is a registration error.
        If you do not use this option you will need to add code to add messages yourself.
        NB, not all templates show system messgaes correctly.</li>
        <li>If you wish to set up values for fields you can do this in the Extra Code boxes.
        For example you could create a user name from first and last name fields, or autogenerate a password.
        Use a hidden field with no value in the Form HTML, put the field name in the Field Names tab here,
        and set the field value in the Extra Code Before box.</li>
        <li>The Extra Code tab allows you to add extra PHP to run before and / or after the Registration.
        Normally you will leave these boxes empty; use them if you need to alter the submitted data in some way.</li>
        <li>The three variables below will contain the Registration results if you wish to do more processing.</li>
        </ul>
        </td>
    </tr>
<?php
        $tooltip = "This object will have all the inserted user data";
        $input = $helper->createTextTD("User object",
            '$MyPlugins->cf_joomla_registration[\'user\']', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Any error messages returned by Joomla if the Registration fails!";
        $input = $helper->createTextTD("Registration messages",
            '$MyPlugins->cf_joomla_registration[\'errors\']', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Returns true or false based on the success of the Registration";
        $input = $helper->createTextTD("Registration result (true/false)",
            '$MyPlugins->cf_joomla_registration[\'complete\']', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));
?>
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

    function onsubmit( $option, $params , $row )
    {
        global $mainframe;

        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'
            .DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();
        $messages[] = 'Inside cf_Joomla_registration::onSubmit()';

        // Check for request forgeries
        //JRequest::checkToken() or die( 'Invalid Token' );

        // Get required system objects
        $user 		= clone(JFactory::getUser());
        $pathway 	=& $mainframe->getPathway();
        $config		=& JFactory::getConfig();
        $authorize	=& JFactory::getACL();
        $document   =& JFactory::getDocument();
        $language =& JFactory::getLanguage();
        $language->load('com_user');
		
		if($row->form_id){
			$formname = CFChronoForm::getFormName($row->form_id);
		}else{
			$formname = JRequest::getVar('chronoformname');
		}
		
        $MyForm =& CFChronoForm::getInstance($formname);
        $MyPlugins =& CFPlugins::getInstance($MyForm->formrow->id);
        // If user registration is not allowed, show 403 not authorized.
        $usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration') == '0') {
        	if($params->get('overrideJallowUserRegistration', '0') != '1'){			
				JError::raiseError( 403, JText::_( 'Access Forbidden' ));
				return;
			}
		}

        // Initialize new usertype setting
        $newUsertype = false;//$usersConfig->get( 'new_usertype' );
        if ( !$newUsertype ) {
			if($params->get('new_usertype', 'Registered')){
				$newUsertype = $params->get('new_usertype', 'Registered');
			}else{
				$newUsertype = 'Registered';
			}
        }

        // execute Extra Code before
        if ( !empty($row->extra4) ) {
            eval( "?>".$row->extra4 );
        }

        // Bind the post array to the user object
        $post = JRequest::get( 'post' );
        $post['username']  = JRequest::getVar($params->get('username'), '', 'post', 'username');
        $post['name']	   = JRequest::getVar($params->get('name'), '', 'post', 'name');
        $post['email']	   = JRequest::getVar($params->get('email'), '', 'post', 'email');
        if ( !$params->get('createpassword') ) {
            $post['password']  = JRequest::getVar($params->get('pass'), '', 'post', 'string');
            $post['password2'] = JRequest::getVar($params->get('vpass'), '', 'post', 'string');
            if ( $params->get('vpass') && $post['password'] != $post['password2'] ) {
                $MyPlugins->cf_joomla_registration['errors'] = JText::_('Passwords do not match');
                $messages[] = JText::_('Passwords do not match');
                if ( $params->get('showmessages') ) {
                    //$mainframe->enqueuemessage(JText::_('Passwords do not match'), 'error');
					$MyForm->addErrorMsg(JText::_('Passwords do not match'));
                }
                if ( $params->get('debugging') ) {
                    $helper->showPluginDebugMessages($messages);
                }
                // remove the password values from display
                $post['password']  = $post[$params->get('pass')]  = '';
                $post['password2'] = $post[$params->get('vpass')] = '';
                //$MyForm->showForm($MyForm->formrow->name, $post);
                return false;
            }else if(!trim($post['password']) && !trim($post['password'])){
				$MyPlugins->cf_joomla_registration['errors'] = JText::_('Password required');
				if ( $params->get('showmessages') ) {
                    //$mainframe->enqueuemessage(JText::_('Passwords do not match'), 'error');
					$MyForm->addErrorMsg(JText::_('Password required'));
                }
				return false;
			}else{
			
			}
        } else {
            jimport('joomla.user.helper');
            $post['password'] = $post['password2'] = JUserHelper::genRandomPassword();
        }
        $messages[] = '$post: '.print_r($post, true);
        if ( !$user->bind( $post, 'usertype' ) ) {
            JError::raiseError( 500, $user->getError());
        }

        // Set some initial user values
        $user->set('id', 0);
        $user->set('usertype', '');
        $user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));

        // TODO: Should this be JDate?
        $user->set('registerDate', date('Y-m-d H:i:s'));

        // If user activation is turned on, we need to set the activation information
        $useractivation = $usersConfig->get('useractivation');
        if ( $useractivation ) {
            jimport('joomla.user.helper');
            $user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
            $user->set('block', '1');
        }

        // If there was an error with registration, set the message and display form
        if ( !$user->save() ) {
            $MyPlugins->cf_joomla_registration['errors'] = JText::_( $user->getError());
            $messages[] = JText::_( $user->getError());
            if ( $params->get('showmessages') ) {
                //$mainframe->enqueuemessage(JText::_( $user->getError()), 'error');
				$MyForm->addErrorMsg(JText::_( $user->getError()));
            }
            if ( $params->get('debugging') ) {
                $helper->showPluginDebugMessages($messages);
            }
            // remove the password values from display
            $post['password']  = $post[$params->get('pass')]  = '';
            $post['password2'] = $post[$params->get('vpass')] = '';
            //$MyForm->showForm($MyForm->formrow->name, $post);
            return false;
        }
        $MyPlugins->cf_joomla_registration['user'] = $user;
        JRequest::setVar('cf_user_id', $user->id);

        // Send registration confirmation mail
        $password = JRequest::getString($params->get('pass'), '', 'post');
        //Disallow control chars in the password
        $password = preg_replace('/[\x00-\x1F\x7F]/', '', $password);

        if ( substr($params->get('emailuser'), 0, 6) != "custom" ) {
            $this->_sendMail($user, $password, $params->get('emailuser'), $params->get('emailadmins'));
        } else {
            $MyForm =& CFChronoForm::getInstance($formname);
            $MyFormEmails =& CFEMails::getInstance($MyForm->formrow->id);
            $emailid = (int)str_replace("custom", "", $params->get('emailuser'));
            $MyFormEmails->emails[$emailid - 1]->enabled = 1;
            $MyFormEmails->emails[$emailid - 1]->template = str_replace("{vlink}", JURI::base()."index.php?option=com_user&task=activate&activation=".$user->get('activation'), $MyFormEmails->emails[$emailid - 1]->template);
            $MyEmail = array($MyFormEmails->emails[$emailid - 1]);
            $MyFormEmails->sendEmails($MyForm, $MyEmail);
        }

        // Everything went fine, set relevant message depending upon user activation state and display message
        $MyPlugins->cf_joomla_registration['complete'] = true;
        if ( $params->get('joomlastatus') ) {
            if ( $useractivation ) {
                echo $message  = JText::_( 'REG_COMPLETE_ACTIVATE' );
            } else {
                if ( $params->get('autologin') ) {
                    echo $message = JText::_( 'REG_COMPLETE' );
                }
            }
        }

        if ( $params->get('autologin') ) {
            $credentials = array();
            $credentials['username'] = $post['username'];
            $credentials['password'] = JRequest::getVar($params->get('pass'), '', 'post', 'string', JREQUEST_ALLOWRAW);
            $mainframe->login($credentials);
        }

        // execute Extra Code before
        if ( !empty($row->extra5) ) {
            eval( "?>".$row->extra5 );
        }

    	if ( $params->get('debugging') ) {
    	    $helper->showPluginDebugMessages($messages);
    	}
    }

    function _sendMail(&$user, $password, $emailuser, $emailadmins)
    {
        global $mainframe;

        $db		=& JFactory::getDBO();
        $language =& JFactory::getLanguage();
        $language->load('com_user');

        $name 		= $user->get('name');
        $email 		= $user->get('email');
        $username 	= $user->get('username');

        $usersConfig 	= &JComponentHelper::getParams( 'com_users' );
        $sitename 		= $mainframe->getCfg( 'sitename' );
        $useractivation = $usersConfig->get( 'useractivation' );
        $mailfrom 		= $mainframe->getCfg( 'mailfrom' );
        $fromname 		= $mainframe->getCfg( 'fromname' );
        $siteURL		= JURI::base();

        $subject 	= sprintf ( JText::_( 'Account details for' ), $name, $sitename);
        $subject 	= html_entity_decode($subject, ENT_QUOTES);

        if ( $useractivation ){
            $message = sprintf ( JText::_( 'SEND_MSG_ACTIVATE' ), $name, $sitename, $siteURL."index.php?option=com_user&task=activate&activation=".$user->get('activation'), $siteURL, $username, $password);
        } else {
            $message = sprintf ( JText::_( 'SEND_MSG' ), $name, $sitename, $siteURL);
        }

        $message = html_entity_decode($message, ENT_QUOTES);

        //get all super administrator
        $query = 'SELECT name, email, sendEmail' .
				' FROM #__users' .
				' WHERE LOWER( usertype ) = "super administrator"';
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		// Send email to user
		if ( ! $mailfrom  || ! $fromname ) {
			$fromname = $rows[0]->name;
			$mailfrom = $rows[0]->email;
		}
		if ( $emailuser == "Yes" ) {
		    JUtility::sendMail($mailfrom, $fromname, $email, $subject, $message);
		}

		// Send notification to all administrators
		$subject2 = sprintf ( JText::_( 'Account details for' ), $name, $sitename);
		$subject2 = html_entity_decode($subject2, ENT_QUOTES);

		// get superadministrators id
		foreach ( $rows as $row ) {
			if (($row->sendEmail)&&($emailadmins == "Yes")) {
				$message2 = sprintf ( JText::_( 'SEND_MSG_ADMIN' ), $row->name, $sitename, $name, $email, $username);
				$message2 = html_entity_decode($message2, ENT_QUOTES);
				JUtility::sendMail($mailfrom, $fromname, $row->email, $subject2, $message2);
			}
		}
	}

	function onload( $option, $row, $params, $html_string )
	{
		global $mainframe;

		$user = JFactory::getUser();
		$db   =& JFactory::getDBO();
		if ( ($user->id) && ($mainframe->isSite()) ) {
			$html_string = 'You can not re-register while you are already signed in';
		}
		return $html_string ;
	}
}
?>