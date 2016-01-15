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

defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'dbutil.php');

/**
 * GCalendar JSON Model
 *
 */
class GCalendarModelJSONFeed extends JModel {

	/**
	 * Returns a Simplepie feed for the calendar according
	 * to the parameter gcid.
	 * If there is a menu available for this calendar the
	 * cache is used and configured from the menu parameters.
	 */
	function getGoogleCalendarFeeds() {
		GCalendarUtil::ensureSPIsLoaded();
		$startDate = JRequest::getVar('start', null);
		$endDate = JRequest::getVar('end', null);

		$browserTz = JRequest::getInt('browserTimezone', null);
		if(!empty($browserTz))
		$browserTz = $browserTz * 60;
		else
		$browserTz = 0;

		$serverOffset = date('Z', $startDate);
		$startDate = $startDate - $browserTz - $serverOffset - GCalendarModelJSONFeed::getGCalendarTZOffset($startDate);
		$endDate = $endDate - $browserTz - $serverOffset - GCalendarModelJSONFeed::getGCalendarTZOffset($endDate);

		$calendarids = '';
		if(JRequest::getVar('gcids', null) != null){
			if(is_array(JRequest::getVar('gcids', null)))
			$calendarids = JRequest::getVar('gcids', null);
			else
			$calendarids = explode(',', JRequest::getVar('gcids', null));
		}else{
			$calendarids = JRequest::getVar('gcid', null);
		}
		$results = GCalendarDBUtil::getCalendars($calendarids);
		if(empty($results))
		return null;

		$calendars = array();
		foreach ($results as $result) {
			if(empty($result->calendar_id))
			continue;

			$feed = new SimplePie_GCalendar();

			if(JRequest::getCmd('layout', null) == 'module'){
				$cacheTime = JRequest::getInt('ctime', -1);
				if($cacheTime > -1){
					// check if cache directory exists and is writeable
					$cacheDir =  JPATH_BASE.DS.'cache'.DS.'mod_gcalendar';
					$cache_exists = true;
					JFolder::create($cacheDir, 0755);
					if ( !is_writable( $cacheDir ) ) {
						JError::raiseWarning( 500, "Created cache at ".$cacheDir." is not writable, disabling cache.");
						$cache_exists = false;
					}else{
						$cache_exists = true;
					}

					$feed->enable_cache($cache_exists);
					if($cache_exists) {
						$feed->set_cache_location($cacheDir);
						$feed->set_cache_duration($cacheTime);
					}
				} else {
					$feed->enable_cache(false);
					$feed->set_cache_duration(-1);
				}
			} else{
				$linkID = GCalendarUtil::getItemId($result->id);
				$menus	= &JSite::getMenu();
				$params = $menus->getParams($linkID);

				$conf =& JFactory::getConfig();
				if ($params != null && ($params->get('gccache', 0) == 2 || ($params->get('gccache', 0) == 1 && $conf->getValue( 'config.caching' )))){
					$cacheTime = $params->get( 'gccache_time', $conf->getValue( 'config.cachetime' ) * 60 );
					// check if cache directory exists and is writeable
					$cacheDir =  JPATH_BASE.DS.'cache'.DS.'com_gcalendar';
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
			}

			$feed->set_show_past_events(FALSE);
			$feed->set_sort_ascending(TRUE);
			$feed->set_orderby_by_start_date(TRUE);
			$feed->set_expand_single_events(TRUE);
			$feed->enable_order_by_date(FALSE);
			$feed->set_start_date($startDate);
			$feed->set_end_date($endDate);
			$feed->set_max_events(1000);
			$feed->put('gcid',$result->id);
			$feed->put('gccolor',$result->color);
			$feed->put('gcname',$result->name);
			$feed->set_cal_language(GCalendarUtil::getFrLanguage());
			$feed->set_timezone(GCalendarUtil::getComponentParameter('timezone'));

			$url = SimplePie_GCalendar::create_feed_url($result->calendar_id, $result->magic_cookie);
			$feed->set_feed_url($url);
			$feed->init();//echo $feed->feed_url;
			if ($feed->error()){
				JError::raiseWarning( 500, 'Simplepie detected an error for the calendar '.$result->calendar_id.'. Please run the <a href="administrator/components/com_gcalendar/libraries/sp-gcalendar/sp_compatibility_test.php">compatibility utility</a>.<br>The following Simplepie error occurred:<br>'.$feed->error());
			}
			$feed->handle_content_type();
			$calendars[] = $feed;
		}

		return $calendars;
	}

	/**
	 * Returns the GCalendar timezone offset in seconds. The given
	 * date is used to be DST compatible.
	 *
	 * @param $date
	 * @return offset in seconds
	 */
	function getGCalendarTZOffset($date = null) {
		static $tzs;
		if($tzs == null){
			$tzs = parse_ini_file(JPATH_SITE.DS.'components'.DS.'com_gcalendar'.DS.'models'.DS.'timezones.ini');
		}
		$tz = GCalendarUtil::getComponentParameter('timezone');
		$offset = '00:00';
		if(!empty($tz)){
			$offset = $tzs[$tz];
		}
		if($date == null) $date = time();

		$dst = GCalendarModelJSONFeed::isDST($date) ? 1 : 0;
		$gcalendarOffset = (((int)substr($offset, 1, 3) - $dst)*60)+substr($offset,3);
		$gcalendarOffset = substr($offset, 0, 1) == '-' ? -1 * $gcalendarOffset : $gcalendarOffset;
		return $gcalendarOffset * 60;
	}

	/**
	 * Checks if the DST applyes to the timezone of GCalendar for the given date.
	 *
	 * @param $date unix timestamp
	 * @param $tz the timezone
	 * @return if is DST
	 */
	function isDST($date, $tz = null){
		if(empty($tz))
		$tz = GCalendarUtil::getComponentParameter('timezone');
		if(class_exists('DateTimeZone') && !empty($tz)){
			$gtz = new DateTimeZone($tz);
			return $gtz->getOffset(new DateTime('2007-01-01 01:00')) != $gtz->getOffset(new DateTime(strftime('%Y-%m-%d %H:%M', $date))) ? true : false;
		}
		return false;
	}

}
