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
 * @author Eric Horne
 * @copyright 2007-2010 Eric Horne
 * @since 2.2.0
 */

defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'nextevents'.DS.'nextevents.php');

class GCalendarEventsHelper {

	function getCalendarItems(&$params) {
		$next = new GCalendarNext($params);
		$events = $next->getCalendarItems();
		if (count($events) > 0) {
			return $events;
		}
		return null;
	}
}
?>
