<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 * @license     GNU/GPLv3 http://www.artio.net/license/gnu-general-public-license
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class SEFControllerConfig extends SEFController
{
    function __construct()
    {
        parent::__construct();
        
        $this->registerTask('apply', 'save');
    }

    function edit()
    {

        JRequest::setVar( 'view', 'config' );

        parent::display();
    }

    function save()
    {
        $model = $this->getModel('config');

        if ($model->store()) {
            $task = JRequest::getCmd('task');
            
            if( $task == 'save' ) {
                $link = 'index.php?option=com_sef';
            }
            elseif( $task == 'apply' ) {
                $link = 'index.php?option=com_sef&controller=config&task=edit';
            }            
            $this->setRedirect($link, JText::_('Configuration updated').' - '.JText::_('INFO_CONFIG_UPDATE'));
        } else {
            $this->setRedirect('index.php?option=com_sef&controller=config&task=dwnld', JText::_('Error writing config'));
        }
    }

    function dwnld()
    {
        $sefConfig =& SEFConfig::getConfig();
        $data =  $sefConfig->saveConfig(1);
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        $data = strtr($data, $trans_tbl);
        $this->output_attachment('configuration.php', $data);
        exit();
    }

    function output_attachment($filename, &$data)
    {
        if (!headers_sent()) {
            header ('Expires: 0');
            header ('Last-Modified: '.gmdate ('D, d M Y H:i:s', time()) . ' GMT');
            header ('Pragma: public');
            header ('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header ('Accept-Ranges: bytes');
            header ('Content-Length: ' . strlen($data));
            header ('Content-Type: Application/octet-stream');
            header ('Content-Disposition: attachment; filename="' . $filename . '"');
            header ('Connection: close');
            ob_end_clean(); //flush the mambo stuff from the ouput buffer
            print $data; // and send the sql
            die();
        }
        else die(JText::_('ERROR_HEADERS'));
    }

    function cancel()
    {
        $this->setRedirect( 'index.php?option=com_sef' );
    }
    
    function setinfotext()
    {
        // Get new state
        $state = JRequest::getVar('state');
        if (is_null($state)) {
            jexit();
        }
        
        $sefConfig =& SEFConfig::getConfig();
        $sefConfig->showInfoTexts = ($state ? true : false);
        $sefConfig->saveConfig(0);
        
        jexit();
    }
}
?>
