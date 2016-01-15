<?php
/** 
* Copyright (c) 2009 Bob Janes
* Author: Bob Janes
* License : GPL
* Visit http://www.ChronoEngine.com for regular update and information.
**/
defined('_JEXEC') or die('Restricted access');
global $mainframe;

require_once( $mainframe->getPath( 'class', 'com_chronocontact' ) );

// the class name must be the same as the file name without the .php at the end
class cf_redirect
{
    //the next 3 fields must be defined for every plugin
    var $result_TITLE = "Redirect";
    var $result_TOOLTIP = "Submit form data to another URL using the Redirect method.
    	Use this plugin to submit data to another website e.g. PayPal
    	that you want the user to visit.";
    var $plugin_name = "cf_redirect"; // must be the same as the class name
    var $event = "ONSUBMIT"; // must be defined and in Uppercase, should be ONSUBMIT or ONLOAD

    // the next function must exist and will have the backend config code
    function show_conf($row, $id, $form_id, $option)
    {
        global $mainframe;

        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'.DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();

        $query = "
        	SELECT *
        		FROM `#__chrono_contact`
        		WHERE id = ".$db->Quote($form_id) ;
        $db->setQuery($query);
        $form = $db->loadObject();

        $htmlstring = $form->html;
        preg_match_all('/name=("|\').*?("|\')/i', $htmlstring, $matches);
        $names = array();
        foreach ( $matches[0] as $name ) {
            $name = preg_replace('/name=("|\')/i', '', $name);
            $name = preg_replace('/("|\')/', '', $name);
            $name = preg_replace('/name=("|\')/', '', $name);
            if ( strpos($name, '[]') ) {
                $name = str_replace('[]', '', $name);
            }
            $names[] = trim($name);
        }
        $names = array_unique($names);

        // identify and initialise the parameters used in this plugin
        $params_array = array(
        	'debugging' => '0',
        	'target_url' => 'http://',
            'onsubmit' => 'before_email');
        $params = $helper->loadParams($row, $params_array);

        $messages[] = '$params: '.print_r($params, true);
        if ( $params->get('debugging') ) {
    	    $helper->showPluginDebugMessages($messages);
    	}
?>
<form action="index2.php" method="post" name="adminForm" id="adminForm" class="adminForm" >
<?php
        echo $pane->startPane("cf_redirect");
        echo $pane->startPanel( 'General', "general" );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('Field names from your form', '', true, $attribs['header']);
        echo $helper->wrapTR($input);

        foreach ( $names as $name ) {
            $tooltip = "Enter the other site field name that matches '$name'";
            $input = $helper->createInputTD("'$name' field",
                "extra2[$name]", $extra2->get($name), '', $attribs['input'], $tooltip);
            echo $helper->wrapTR($input, array('class' => 'cf_config'));
        }

        $input = $helper->createHeaderTD('Extra field values to send', '',
            true, array('colspan' => '4', 'class' => 'cf_header'));
        echo $helper->wrapTR($input);

        $tooltip = "Extra Fields, enter data in this format : ship_to_name=field_name<br />Take care to add each entry to a new line";
        $input = $helper->createTextareaTD('Extra fields Data', 'extra1', $row->extra1,  $attribs['textarea'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));
?>
</table>
<?php
        echo $pane->endPanel();
        echo $pane->startPanel( "URL parameters", 'url_params' );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('Plugin parameters', '',
            true, array('colspan' => '4', 'class' => 'cf_header'));
        echo $helper->wrapTR($input);

        $tooltip = "The target URL to redirect to";
        $input = $helper->createInputTD("Target URL",
            "params[target_url]", $params->get('target_url'), '', $attribs['input'], $tooltip);
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

        $tooltip = "Execute some code just before the Redirect transaction is executed";
        $input = $helper->createTextareaTD('Extra before CURL code', 'extra4',
            $row->extra4, $attribs['textarea'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        /*$tooltip = "Execute some code just after the CURL transaction is executed";
        $input = $helper->createTextareaTD('Extra after CURL code', 'extra5',
            $row->extra5, $attribs['textarea'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));*/
 ?>
</table>
<?php
         echo $pane->endPanel();
         echo $pane->startPanel( 'Help', 'help' );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('How to configure the Redirect plugin', '',
            true, array('colspan' => '4', 'class' => 'cf_header'));
        echo $helper->wrapTR($input);
?>
    <tr>
        <td colspan='4' style='border:1px solid silver; padding:6px;'>
        <div>The plugin allows you to use the Redirect URL to send data and the user from a ChronoForms Form to
        another site or to another application on your site. Use this when you need to redirect the user
        for example to PayPal to authorise a payment, if you just want to transfer data from the form and keep teh user on
        your site, then the CURL plugin is better.</div>
        <ul><li>First, open the URL params tab and put the url that you want to submit the information to
        in the Target URL box e.g. http://www.paypal.com/cgi-bin/webscr </li>
        <li>Next click the General Tab and you will see a list of the fields in your form.
        Put the corresponding names for the site you are sending to in the boxes along-side
        (often these will be the same as the field names in your form.)</li>
        <li>In the Extra Fields Data box you can enter values for fields that will be the same
        for each submission. These will often be client or transaction identifiers for the other site.
        (Note: Putting this information here means that it is never exposed in your form.)</li>
        <li>Lastly on the Redirect params tab, you can set the Redirect Plugin to run before or after Emails are sent.
        Use this if you need to fine-tune the process flow. Note that the Redirection will be done after any emails are
        sent and the data saved.</li>
        <li>The Extra Code tab allows you to add extra PHP to run before the Redirection Plugin
        (running code after redirection is not possible).
        Normally you will leave these boxes empty; use them if you need to alter the submitted data in some way.</li>
        </ul>
        </td>
    </tr>
    </table>
<?php
        echo $pane->endPane();
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
        if ( $style ) $doc->addStyleDeclaration($style);
        if ( $script ) $doc->addScriptDeclaration($script);

    }

    // this function must exist and may not be changed unless you need to customize something
    function save_conf( $option )
    {
        global $mainframe;

        // turn extras2 into params list
        //$mainframe->enqueuemessage('$_POST: '.print_r($_POST, true));
        //$extra1 = JRequest::getVar( 'extra1', '', 'post', 'array' );
        //$mainframe->enqueuemessage('Extra1: '.print_r($extra1, true));

        //$extra2 = JRequest::getVar( 'extra2', '', 'post', 'array' );
        //$mainframe->enqueuemessage('Extra2: '.print_r($extra2, true));


        //$mainframe->enqueuemessage(print_r($row->extra2, true));
        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'
            .DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();
        $helper->save_conf($option);
    }

    /**
     * The function that will be executed when the form is submitted
     *
     */
    function onsubmit( $option, $params, $row )
    {
        global $mainframe;

        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'
            .DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();

        $doc =& JFactory::getDocument();
        $doc->addStyleDeclaration("div.debug {border:1px solid red; padding:3px; margin-bottom:3px;}");
        $mainframe->enqueuemessage('$_POST: '.print_r($_POST, true));
        $messages = array();

        /*********do the before onsubmit code**********/
        if ( !empty($row->extra4) ) {
            eval( "?>".$row->extra4 );
        }

        $url_values = array();
        /// add main fields

        if ( trim($row->extra2) ) {
            $extras2 = explode("\n", $row->extra2);
            foreach ( $extras2 as $extra2 ) {
                $values = array();
                $values = explode( "=", $extra2 );
                if ( $values[1] ) {
                    $v = urlencode(trim($values[1]));
                    $url_values[$v] = JRequest::getVar(trim($values[0]), '', 'post', 'string', '');
                }
            }
        }

        if ( trim($row->extra1) ) {
            $extras = explode("\n", $row->extra1);
            foreach ( $extras as $extra ) {
                // Note: accept only the first parameter pair on each line
                $values = explode("=", $extra, 2);
                if ( isset($values[1]) ) {
                    $url_values[$values[0]] = trim($values[1]);
                }
            }
        }

        $query = JURI::buildQuery($url_values);
        $uri = $params->get('target_url').'?'.$query;
        $parts['query'] = $query;
        $parts['scheme'] = 'http';
        $parts['host'] = 'bobjanes.com';
        //$parts['host'] = $params->get('target_url');
        //$uri = JURI::toString($parts);

        $MyForm =& CFChronoForm::getInstance();
        $MyForm->formrow->redirecturl = $uri;

        $messages[] = '<b>cf_redirect debug info</b>';
        $messages[] = '$url: '.print_r($uri, true);
        $messages[] = '$_POST: '.print_r($_POST, true);
        /*
         * Build query into url and set CF redirect url
         */


    	$helper->showCFDebugMessage('Redirect URL set');

    	if ( $params->get('debugging') ) {
    	    $helper->showPluginDebugMessages($messages);
    	}
	}
}
?>