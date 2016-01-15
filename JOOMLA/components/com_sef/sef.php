<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

require_once('sef.router.php');

class JoomSEFController extends JController
{
    function display()
    {
        $this->setRedirect(JURI::root());
    }
}

$cmd = JRequest::getCmd('controller');
$classname = 'JoomSEFController'.$cmd;

if (!class_exists($classname)) {
    $file = JPATH_COMPONENT.DS.'controllers'.DS.$cmd.'.php';
    if (file_exists($file)) {
        require_once($file);
    }
    else {
        $classname = 'JoomSEFController';
    }
    
    if (!class_exists($classname)) {
        JError::raiseError(403, JText::_('Access Forbidden'));
    }
}

$controller = new $classname();
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
