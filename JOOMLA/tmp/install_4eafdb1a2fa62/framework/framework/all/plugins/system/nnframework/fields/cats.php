<?php
/**
 * Element: Categories
 * Displays a (multiple) selectbox of available sections and categories
 *
 * @package     NoNumber! Framework
 * @version     11.10.5
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2011 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die();

/**
 * Categories Element
 */
class nnFieldCats
{
	var $_version = '11.10.5';

	function getInput( $name, $id, $value, $params, $children, $j15 = 0 )
	{
		$this->params = $params;

		$size = (int) $this->def( 'size' );
		$multiple = $this->def( 'multiple' );
		$show_uncategorized = $this->def( 'show_uncategorized' );
		$auto_select_cats = $this->def( 'auto_select_cats', 1 );

		$db =& JFactory::getDBO();

		// assemble items to the array
		$options = array();
		if ( $show_uncategorized ) {
			$options[] = JHTML::_( 'select.option', '0', JText::_('Uncategorized'), 'value', 'text', 0 );
		}

		$options = JHtml::_( 'category.options', 'com_content' );

		foreach ( $options as $i => $item ) {
			$item->text = str_replace( '- ', '&nbsp;&nbsp;', str_replace( '&#160;', '&nbsp;', $item->text ) );
			$options[$i] = $item;
		}

		require_once JPATH_PLUGINS.'/system/nnframework/helpers/html.php';
		return nnHTML::selectlist( $options, $name, $value, $id, $size, $multiple, 0, $j15 );
	}

	private function def( $val, $default = '' )
	{
		return ( isset( $this->params[$val] ) && (string) $this->params[$val] != '' ) ? (string) $this->params[$val] : $default;
	}
}

if ( version_compare( JVERSION, '1.6.0', 'l' ) ) {
	// For Joomla 1.5
	class JElementNN_Cats extends JElement
	{
		/**
		 * Element name
		 *
		 * @access	protected
		 * @var		string
		 */
		var $_name = 'Cats';

		function fetchElement( $name, $value, &$node, $control_name )
		{
			$this->_nnfield = new nnFieldCats();
			return $this->_nnfield->getInput( $control_name.'['.$name.']', $control_name.$name, $value, $node->attributes(), $node->children(), 1 );
		}
	}
} else {
	// For Joomla 1.6
	class JFormFieldNN_Cats extends JFormField
	{
		/**
		 * The form field type
		 *
		 * @var		string
		 */
		public $type = 'Cats';

		protected function getInput()
		{
			$this->_nnfield = new nnFieldCats();
			return $this->_nnfield->getInput( $this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children() );
		}
	}
}