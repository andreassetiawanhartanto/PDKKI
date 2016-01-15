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

defined('_JEXEC') or die('Restricted access');
$data = array();
$SECSINDAY=86400;
if(!empty($this->calendars)){
	foreach ($this->calendars as $calendar){
		$itemID = GCalendarUtil::getItemId($calendar->get('gcid'));
		$menus	= &JSite::getMenu();
		$params = $menus->getParams($itemID);
		if(empty($params))
		$params = new JParameter('');
		$dateformat = $params->get('description_date_format', '%d.%m.%Y');
		$timeformat = $params->get('description_time_format', '%H:%M');
		$event_display = $params->get('description_format', '<p>{startdate} {starttime} {dateseparator} {enddate} {endtime}<br/>{description}</p>');

		if(!empty($itemID)) {
			$itemID = '&Itemid='.$itemID;
		} else {
			$menu=JSite::getMenu();
			$activemenu=$menu->getActive();
			if($activemenu != null)
			$itemID = '&Itemid='.$activemenu->id;
		}

		$items = $calendar->get_items();
		foreach ($items as $event) {
			$allDayEvent = $event->get_day_type() == $event->SINGLE_WHOLE_DAY || $event->get_day_type() == $event->MULTIPLE_WHOLE_DAY;
			$description = GCalendarUtil::renderEvent($event, $event_display, $dateformat, $timeformat);
			if(strlen($description) > 200)
			$description = substr($description, 0, 196).' ...';
			$data[] = array(
			'id' => $event->get_id(),
			'title' => htmlspecialchars_decode($event->get_title()),
			'start' => strftime('%Y-%m-%dT%H:%M:%S', $event->get_start_date()),
			'end' => strftime('%Y-%m-%dT%H:%M:%S',$allDayEvent? $event->get_end_date() - $SECSINDAY:$event->get_end_date()),
			'url' => JRoute::_('index.php?option=com_gcalendar&view=event&eventID='.$event->get_id().'&start='.$event->get_start_date().'&end='.$event->get_end_date().'&gcid='.$calendar->get('gcid').$itemID),
			'className' => "gcal-event_gccal_".$calendar->get('gcid'),
			'allDay' => $allDayEvent,
			'description' => $description
			);
		}
	}
}
echo json_encode($data);
?>