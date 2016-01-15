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
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'config.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'seftools.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'controller.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.'crawler.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'sefurls.php');

class JoomSEFControllerCron extends JController
{
    function display()
    {
        $this->setRedirect(JURI::root());
    }
    
}