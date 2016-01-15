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
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'dbutil.php');

/**
 * GCalendar Model
 *
 */
class GCalendarModelEvent extends JModel
{

	/**
	 * Gets the simplepie event
	 * @return string event
	 */
	function getGCalendar()
	{
		GCalendarUtil::ensureSPIsLoaded();
		$results = GCalendarDBUtil::getCalendars(JRequest::getVar('gcid', null));
		if(empty($results) || JRequest::getVar('eventID', null) == null)
		return null;
		$result = $results[0];

		$feed = new SimplePie_GCalendar();
		$feed->set_show_past_events(FALSE);
		$feed->set_sort_ascending(TRUE);
		$feed->set_orderby_by_start_date(TRUE);
		$feed->set_expand_single_events(TRUE);
		$feed->enable_order_by_date(FALSE);
		$feed->enable_cache(FALSE);
		$feed->set_start_date((JRequest::getVar('start', 0)-86400));
		$feed->set_end_date((JRequest::getVar('end', 0)+86400));
		$feed->put('gcid',$result->id);
		$feed->put('gccolor',$result->color);
		$feed->put('gcname',$result->name);
		$feed->set_cal_language(GCalendarUtil::getFrLanguage());
		$feed->set_timezone(GCalendarUtil::getComponentParameter('timezone'));

		$url = SimplePie_GCalendar::create_feed_url($result->calendar_id, $result->magic_cookie);
		$feed->set_feed_url($url);
		$feed->init();
		if ($feed->error()){
			JError::raiseWarning( 500, 'Simplepie detected an error for the calendar '.$result->calendar_id.'. Please run the <a href="administrator/components/com_gcalendar/libraries/sp-gcalendar/sp_compatibility_test.php">compatibility utility</a>.<br>The following Simplepie error occurred:<br>'.$feed->error());
		}
		$feed->handle_content_type();
		$items = $feed->get_items();
		foreach ($items as $item) {
			if($item->get_id() == JRequest::getVar('eventID', null))
			return $item;
		}
		return null;
	}

}
