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

jimport( 'joomla.application.component.view');

/**
 * Raw View class for the GCalendar Component
 *
 */
class GCalendarViewJSONFeed extends JView
{
	function display($tpl = null)
	{
		$calendars = $this->get( 'GoogleCalendarFeeds' );
		if(!is_array($calendars))
		$calendars = array();
		$this->assignRef( 'calendars',	$calendars );

		if (!function_exists('json_encode'))
		{
			function json_encode($a=false)
			{
				if (is_null($a)) return 'null';
				if ($a === false) return 'false';
				if ($a === true) return 'true';
				if (is_scalar($a))
				{
					if (is_float($a))
					{
						// Always use "." for floats.
						return floatval(str_replace(",", ".", strval($a)));
					}

					if (is_string($a))
					{
						static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
						return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
					}
					else
					return $a;
				}
				$isList = true;
				for ($i = 0, reset($a); $i < count($a); $i++, next($a))
				{
					if (key($a) !== $i)
					{
						$isList = false;
						break;
					}
				}
				$result = array();
				if ($isList)
				{
					foreach ($a as $v) $result[] = json_encode($v);
					return '[' . join(',', $result) . ']';
				}
				else
				{
					foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
					return '{' . join(',', $result) . '}';
				}
			}
		}

		parent::display($tpl);
	}
}
?>
