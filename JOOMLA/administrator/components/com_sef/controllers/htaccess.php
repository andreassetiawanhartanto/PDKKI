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

class SEFControllerHtaccess extends JController
{
    function display()
    {
        
        JRequest::setVar( 'view', 'htaccess' );
        JRequest::setVar( 'layout', 'simple' );

        parent::display();
    }
    
}
?>