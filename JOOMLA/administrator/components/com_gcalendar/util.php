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

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'dbutil.php');

/**
 * A util class with some static helper methodes used in GCalendar.
 *
 * @author allon
 */
class GCalendarUtil{

	/**
	 * Loads the simplepie Libraries the correct way.
	 */
	function ensureSPIsLoaded(){
		if(!class_exists('SimplePie')){
			jimport('simplepie.simplepie');
		}

		if(!class_exists('SimplePie_GCalendar')){
			require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'sp-gcalendar'.DS.'simplepie-gcalendar.php');
		}
	}

	/**
	 * Loads JQuery if the component parameter is set to yes.
	 */
	function loadJQuery(){
		static $jQueryloaded;
		if($jQueryloaded == null){
			$param   = GCalendarUtil::getComponentParameter('loadJQuery');
			$document =& JFactory::getDocument();
			if(!JFactory::getApplication()->get('jquery', false) && ($param == 'yes' || empty($param))){
				$document->addScript(JURI::base().'components/com_gcalendar/libraries/jquery/jquery-1.3.2.min.js');
				JFactory::getApplication()->set('jquery', true);
			}
			$document->addScriptDeclaration("jQuery.noConflict();");
			$jQueryloaded = 'loaded';
		}
	}

	/**
	 * Returns the component parameter for the given key.
	 *
	 * @param $key
	 * @param $defaultValue
	 * @return the component parameter
	 */
	function getComponentParameter($key, $defaultValue = null){
		$params   = JComponentHelper::getParams('com_gcalendar');
		return $params->get($key, $defaultValue);
	}

	/**
	 * Returns the correct configured frontend language for the
	 * joomla web site.
	 * The format is something like de-DE which can be passed to google.
	 *
	 * @return the frontend language
	 */
	function getFrLanguage(){
		$conf	=& JFactory::getConfig();
		return $conf->getValue('config.language');
		//		$params   = JComponentHelper::getParams('com_languages');
		//		return $params->get('site', 'en-GB');
	}

	/**
	 * Returns a valid Item ID for the given calendar id. If none is found
	 * NULL is returned.
	 *
	 * @param $cal_id
	 * @return the item id
	 */
	function getItemId($cal_id){
		$component	= &JComponentHelper::getComponent('com_gcalendar');
		$menu = &JSite::getMenu();
		$items		= $menu->getItems('componentid', $component->id);

		if (is_array($items)){
			foreach($items as $item) {
				$paramsItem	=& $menu->getParams($item->id);
				$calendarids = $paramsItem->get('calendarids');
				if(empty($calendarids)){
					$results = GCalendarDBUtil::getAllCalendars();
					if($results){
						$calendarids = array();
						foreach ($results as $result) {
							$calendarids[] = $result->id;
						}
					}
				}
				$contains_gc_id = FALSE;
				if ($calendarids){
					if( is_array( $calendarids ) ) {
						$contains_gc_id = in_array($cal_id,$calendarids);
					} else {
						$contains_gc_id = $cal_id == $calendarids;
					}
				}
				if($contains_gc_id){
					return $item->id;
				}
			}
		}
		return null;
	}

	/**
	 * The simplepie event is rendered for the given formats and
	 * returned as HTML code.
	 *
	 * @param $event
	 * @param $format
	 * @param $dateformat
	 * @param $timeformat
	 * @return the HTML code of the efent
	 */
	function renderEvent($event, $format, $dateformat, $timeformat){
		$feed = $event->get_feed();
		$tz = GCalendarUtil::getComponentParameter('timezone');
		if($tz == ''){
			$tz = $feed->get_timezone();
		}

		$itemID = GCalendarUtil::getItemId($feed->get('gcid'));
		if(!empty($itemID)){
			$itemID = '&Itemid='.$itemID;
		}else{
			$menu=JSite::getMenu();
			$activemenu=$menu->getActive();
			if($activemenu != null)
			$itemID = '&Itemid='.$activemenu->id;
		}

		// These are the dates we'll display
		$startDate = self::strftime($dateformat, $event->get_start_date());
		$startTime = self::strftime($timeformat, $event->get_start_date());
		$endDate = self::strftime($dateformat, $event->get_end_date());
		$endTime = self::strftime($timeformat, $event->get_end_date());

		$temp_event = $format;

		switch($event->get_day_type()){
			case $event->SINGLE_WHOLE_DAY:
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}","",$temp_event);
				$temp_event=str_replace("{dateseparator}","",$temp_event);
				$temp_event=str_replace("{enddate}","",$temp_event);
				$temp_event=str_replace("{endtime}","",$temp_event);
				break;
			case $event->SINGLE_PART_DAY:
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}",$startTime,$temp_event);
				$temp_event=str_replace("{dateseparator}","-",$temp_event);
				$temp_event=str_replace("{enddate}","",$temp_event);
				$temp_event=str_replace("{endtime}",$endTime,$temp_event);
				break;
			case $event->MULTIPLE_WHOLE_DAY:
				$SECSINDAY=86400;
				$endDate = self::strftime($dateformat, $event->get_end_date()-$SECSINDAY);
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}","",$temp_event);
				$temp_event=str_replace("{dateseparator}","-",$temp_event);
				$temp_event=str_replace("{enddate}",$endDate,$temp_event);
				$temp_event=str_replace("{endtime}","",$temp_event);
				break;
			case $event->MULTIPLE_PART_DAY:
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}",$startTime,$temp_event);
				$temp_event=str_replace("{dateseparator}","-",$temp_event);
				$temp_event=str_replace("{enddate}",$endDate,$temp_event);
				$temp_event=str_replace("{endtime}",$endTime,$temp_event);
				break;
		}

		if (substr_count($temp_event, '"{description}"')){
			// If description is in html attribute
			$desc = htmlspecialchars(str_replace('"',"'",$event->get_description()));
		}else{
			//Make any URLs used in the description also clickable
			$desc = preg_replace("@(src|href)=\"https?\://@i",'\\1="',$event->get_description());
			$desc = preg_replace("@(((f|ht)tps?://)[^\"\'\>\s]+)@",'<a href="\\1" target="_blank">\\1</a>', $desc);
			//or "¤(((f|ht)tp:\/\/)[\-a-zA-Z0-9@:%_\+\.~#\?,\/=&;]+)¤"
		}

		$temp_event=str_replace("{title}",$event->get_title(),$temp_event);
		$temp_event=str_replace("{description}",$desc,$temp_event);
		$temp_event=str_replace("{where}",$event->get_location(),$temp_event);
		$temp_event=str_replace("{backlink}",htmlentities(JRoute::_('index.php?option=com_gcalendar&view=event&eventID='.$event->get_id().'&start='.$event->get_start_date().'&end='.$event->get_end_date().'&gcid='.$feed->get('gcid').$itemID)),$temp_event);
		$temp_event=str_replace("{link}",$event->get_link().'&ctz='.$tz,$temp_event);
		$temp_event=str_replace("{maplink}","http://maps.google.com/?q=".urlencode($event->get_location()),$temp_event);
		$temp_event=str_replace("{calendarname}",$feed->get('gcname'),$temp_event);
		$temp_event=str_replace("{calendarcolor}",$feed->get('gccolor'),$temp_event);
		// Accept and translate HTML
		$temp_event = html_entity_decode($temp_event);
		return $temp_event;
	}

	/**
	 * Returns the faded color for the given color.
	 *
	 * @param $color
	 * @param $percentage
	 * @return the faded color
	 */
	function getFadedColor($color, $percentage = 85) {
		$percentage = 100 - $percentage;
		$rgbValues = array_map( 'hexDec', GCalendarUtil::str_split( ltrim($color, '#'), 2 ) );

		for ($i = 0, $len = count($rgbValues); $i < $len; $i++) {
			$rgbValues[$i] = decHex( floor($rgbValues[$i] + (255 - $rgbValues[$i]) * ($percentage / 100) ) );
		}

		return '#'.implode('', $rgbValues);
	}

	/**
	 * The php string split method for beeing php 4 compatible.
	 *
	 */
	function str_split($string,$string_length=1) {
		if(strlen($string)>$string_length || !$string_length) {
			do {
				$c = strlen($string);
				$parts[] = substr($string,0,$string_length);
				$string = substr($string,$string_length);
			} while($string !== false);
		} else {
			$parts = array($string);
		}
		return $parts;
	}

	/**
	 * Translates day of week number to a string.
	 * Joomla 1.6 compatibility, JDate::dayToString is protected
	 *
	 * @param	integer	The numeric day of the week.
	 * @param	boolean	Return the abreviated day string?
	 * @return	string	The day of the week.
	 */
	function dayToString($day, $abbr = false)
	{
		switch ($day) {
			case 0: return $abbr ? JText::_('SUN') : JText::_('SUNDAY');
			case 1: return $abbr ? JText::_('MON') : JText::_('MONDAY');
			case 2: return $abbr ? JText::_('TUE') : JText::_('TUESDAY');
			case 3: return $abbr ? JText::_('WED') : JText::_('WEDNESDAY');
			case 4: return $abbr ? JText::_('THU') : JText::_('THURSDAY');
			case 5: return $abbr ? JText::_('FRI') : JText::_('FRIDAY');
			case 6: return $abbr ? JText::_('SAT') : JText::_('SATURDAY');
		}
	}

	/**
	 * Translates month number to a string.
	 * Joomla 1.6 compatibility, JDate::monthToString is protected
	 *
	 * @param	integer	The numeric month of the year.
	 * @param	boolean	Return the abreviated month string?
	 * @return	string	The month of the year.
	 */
	function monthToString($month, $abbr = false)
	{
		switch ($month) {
			case 1:  return $abbr ? JText::_('JANUARY_SHORT')	: JText::_('JANUARY');
			case 2:  return $abbr ? JText::_('FEBRUARY_SHORT')	: JText::_('FEBRUARY');
			case 3:  return $abbr ? JText::_('MARCH_SHORT')		: JText::_('MARCH');
			case 4:  return $abbr ? JText::_('APRIL_SHORT')		: JText::_('APRIL');
			case 5:  return $abbr ? JText::_('MAY_SHORT')		: JText::_('MAY');
			case 6:  return $abbr ? JText::_('JUNE_SHORT')		: JText::_('JUNE');
			case 7:  return $abbr ? JText::_('JULY_SHORT')		: JText::_('JULY');
			case 8:  return $abbr ? JText::_('AUGUST_SHORT')	: JText::_('AUGUST');
			case 9:  return $abbr ? JText::_('SEPTEMBER_SHORT')	: JText::_('SEPTEMBER');
			case 10: return $abbr ? JText::_('OCTOBER_SHORT')	: JText::_('OCTOBER');
			case 11: return $abbr ? JText::_('NOVEMBER_SHORT')	: JText::_('NOVEMBER');
			case 12: return $abbr ? JText::_('DECEMBER_SHORT')	: JText::_('DECEMBER');
		}
	}

	function strftime($dateFormat,$date){
		return JFactory::getDate()->_strftime($dateFormat, $date);
	}
}
?>