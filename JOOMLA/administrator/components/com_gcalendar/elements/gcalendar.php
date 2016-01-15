<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Allon Moritz
 * @copyright 2007-2010 Allon Moritz
 * @since 2.2.0
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

/**
 *
 * @package 	GCalendar
 * @subpackage	Parameter
 * @since		1.5
 */

class JElementGCalendar extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'GCalendar';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$section	= $node->attributes('section');
		$class		= $node->attributes('class');
		if (!$class) {
			$class = "inputbox";
		}

		if (!isset ($section)) {
			// alias for section
			$section = $node->attributes('scope');
			if (!isset ($section)) {
				$section = 'content';
			}
		}

		$query = 'SELECT id, name, calendar_id FROM #__gcalendar';
		$db->setQuery($query);
		$options = $db->loadObjectList();
		$result = '<select name="'.$control_name.'['.$name.'][]" id="'.$name.'" class="'.$class.'" multiple="multiple">';
		
		foreach( $options as $option ) {
			$display_name = $option->name;
			if(is_array( $value) ) {
				if( in_array( $option->id, $value ) ) {
					$result .= '<option selected="true" value="'.$option->id.'" >'.$display_name.'</option>';
				} else {
					$result .= '<option value="'.$option->id.'" >'.$display_name.'</option>';
				}
			} elseif ( $value ) {
				if( $value == $option->id ) {
					$result .= '<option selected="true" value="'.$option->id.'" >'.$display_name.'</option>';
				} else {
					$result .= '<option value="'.$option->id.'" >'.$display_name.'</option>';
				}
			} elseif ( !( $value ) ) {
				$result .= '<option value="'.$option->id.'" >'.$display_name.'</option>';
			}
		}
		$result .= '</select>';
		return $result;
		
	}
}
