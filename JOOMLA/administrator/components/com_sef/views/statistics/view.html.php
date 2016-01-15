<?php
/*
 * Created on 19.10.2011
 */
 
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class SEFViewStatistics extends JView {
	function display($tpl=null) {
		$icon = 'statistics.png';
            JToolbarHelper::title(JText::_('COM_SEF_STATISTICS'), $icon);
            JToolBarHelper::back('COM_SEF_BACK', 'index.php?option=com_sef');
		
		parent::display($tpl);

	}
	
}
?>