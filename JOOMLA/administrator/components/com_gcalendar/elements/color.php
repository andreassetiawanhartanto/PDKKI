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

class JElementColor extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Color';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$document = &JFactory::getDocument();
		$document->addScript(JURI::base(). 'components/com_gcalendar/libraries/jscolor/jscolor.js' );
		return '<input class="color" value="'.$value.'" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'"/>';
		
	}
}
