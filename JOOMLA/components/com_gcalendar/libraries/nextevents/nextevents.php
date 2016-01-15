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
 * This code was based on Allon Moritz's great work in the companion
 * upcoming module.
 *
 * @author Eric Horne
 * @copyright 2009-2010 Eric Horne
 * @since 2.2.0
 */

defined('_JEXEC') or die('Restricted access');


require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'dbutil.php');

class GCalendarNext {

	var $params = "";

	function GCalendarNext(&$params) {
		$this->params = $params;
	}


	function getCalendarItems() {
		GCalendarUtil::ensureSPIsLoaded();
		$params = $this->params;
		$calendarids = $params->get('calendarids');
		$results = GCalendarDBUtil::getCalendars($calendarids);
		if(empty($results)){
			JError::raiseWarning( 500, 'The selected calendar(s) were not found in the database.');
			return array();
		}
		$values = array();
		$sortOrder = $params->get( 'order', 1 )==1;
		$maxEvents = $params->get('max_events', 10);
		foreach ($results as $result) {
			if(!empty($result->calendar_id)){
				$feed = new SimplePie_GCalendar();
				$feed->set_show_past_events($params->get('past_events', TRUE));
				$startDate = $params->get('start_date', '');
				$endDate = $params->get('end_date', '');
				if(!empty($startDate) && !empty($endDate)){
					$feed->set_start_date(strtotime($startDate));
					$feed->set_end_date(strtotime($endDate));
				}
				$feed->set_sort_ascending(TRUE);
				$feed->set_orderby_by_start_date($sortOrder);
				$feed->set_expand_single_events($params->get('expand_events', TRUE));
				$feed->enable_order_by_date(false);

				$conf =& JFactory::getConfig();
				if ($params != null && ($params->get('gc_cache', 0) == 2 || ($params->get('gc_cache', 0) == 1 && $conf->getValue( 'config.caching' )))){
					$cacheTime = $params->get( 'gccache_time', $conf->getValue( 'config.cachetime' ) * 60 );
					// check if cache directory exists and is writeable
					$cacheDir =  JPATH_BASE.DS.'cache'.DS.$params->get('gc_cache_folder', '');
					JFolder::create($cacheDir, 0755);
					if ( !is_writable( $cacheDir ) ) {
						JError::raiseWarning( 500, "Created cache at ".$cacheDir." is not writable, disabling cache.");
						$cache_exists = false;
					}else{
						$cache_exists = true;
					}

					//check and set caching
					$feed->enable_cache($cache_exists);
					if($cache_exists) {
						$feed->set_cache_location($cacheDir);
						$feed->set_cache_duration($cacheTime);
					}
				} else {
					$feed->enable_cache(false);
					$feed->set_cache_duration(-1);
				}

				$feed->set_max_events($maxEvents);
				$feed->set_timezone(GCalendarUtil::getComponentParameter('timezone'));
				$feed->set_cal_language(GCalendarUtil::getFrLanguage());
				$feed->set_cal_query($params->get('find', ''));
				$feed->put('gcid',$result->id);
				$feed->put('gcname',$result->name);
				$feed->put('gccolor',$result->color);
				$url = SimplePie_GCalendar::create_feed_url($result->calendar_id, $result->magic_cookie);

				$feed->set_feed_url($url);
				// Initialize the feed so that we can use it.
				$feed->init();
				//				echo $feed->feed_url;

				if ($feed->error()){
					JError::raiseWarning(500, 'Simplepie detected an error. Please run the <a href="administrator/components/com_gcalendar/libraries/sp-gcalendar/sp_compatibility_test.php">compatibility utility</a>.', $feed->error());
				}

				// Make sure the content is being served out to the browser properly.
				$feed->handle_content_type();

				$values = array_merge($values, $feed->get_items());
			}
		}

		// we sort the array based on the event compare function
		usort($values, array("SimplePie_Item_GCalendar", "compare"));

		$events = array_filter($values, array($this, "filter"));

		$offset = $params->get('offset', 0);
		$numevents = $params->get('count', $maxEvents);

		$events = array_slice($values, $offset, $numevents);

		//return the feed data structure for the template
		return $events;
	}

	function filter($event) {
		$filter = $this->params->get('title_filter', '.*');

		if (!preg_match('/'.$filter.'/', $event->get_title())) {
			return false;
		}
		if ($event->get_end_date() > time()) {
			return true;
		}

		return false;
	}
}
?>

