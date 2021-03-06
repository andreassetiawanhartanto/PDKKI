<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class SEFViewLogger extends SEFView
{
	function display($tpl = null)
	{
		JToolBarHelper::title(JText::_('COM_SEF_LOGS_TITLE'), '404-logs.png');
		
		JToolBarHelper::custom('clear', 'clear', '', 'COM_SEF_LOGS_CLEAR', false);
		JToolBarHelper::spacer();
		JToolBarHelper::back('Back', 'index.php?option=com_sef');
		
		// Get data from the model
        $this->assignRef('items', $this->get('Data'));
        $this->assignRef('total', $this->get('Total'));
        $this->assignRef('lists', $this->get('Lists'));
        $this->assignRef('pagination', $this->get('Pagination'));
        
        $enabled = $this->get('Enabled');
        if (!$enabled) {
	       $app =& JFactory::getApplication();
           $app->enqueueMessage(JText::_('COM_SEF_LOGS_DISABLED_NOTICE')); 
        }
        
        JHTML::_('behavior.tooltip');
        
		parent::display($tpl);
	}

}
