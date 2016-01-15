<?php
/*
 * Created on 19.10.2011
 */
 
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');

class SEFControllerStatistics extends SEFController {
    function display()
    {
        JRequest::setVar('view', 'statistics');
        
        parent::display();
    }
    
}
?>