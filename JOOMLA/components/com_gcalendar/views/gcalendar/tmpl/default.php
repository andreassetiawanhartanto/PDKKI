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
JHTML::_('behavior.mootools');
GCalendarUtil::loadJQuery();
$document = &JFactory::getDocument();
$document->addScript(JURI::base(). 'components/com_gcalendar/libraries/fullcalendar/fullcalendar.min.js' );
$document->addStyleSheet(JURI::base().'components/com_gcalendar/libraries/fullcalendar/fullcalendar.css');
$document->addScript(JURI::base().'components/com_gcalendar/libraries/jquery/ui/jquery-ui-1.7.3.custom.min.js');
$document->addScript(JURI::base().'components/com_gcalendar/libraries/jquery/ext/jquery.ba-hashchange.min.js');
$document->addScript(JURI::base().'components/com_gcalendar/libraries/jquery/ext/jquery.qtip-1.0.0.min.js');
$document->addStyleDeclaration("#ui-datepicker-div { z-index: 15; }");

$params = $this->params;

$theme = $params->get('theme', '');
if(JRequest::getVar('theme', null) != null)
$theme = JRequest::getVar('theme', null);
if(!empty($theme))
$document->addStyleSheet(JURI::base().'components/com_gcalendar/libraries/jquery/themes/'.$theme.'/jquery-ui-1.7.3.custom.css');
else
$document->addStyleSheet(JURI::base().'components/com_gcalendar/libraries/jquery/themes/ui-lightness/jquery-ui-1.7.3.custom.css');

$calendarids = array();
$tmp = $params->get('calendarids');
if(is_array($tmp))
$calendarids = $tmp;
else if(!empty($tmp))
$calendarids[] = $tmp;
$allCalendars = GCalendarDBUtil::getAllCalendars();

$calsSources = "		eventSources: [\n";
foreach($allCalendars as $calendar) {
	$cssClass = "gcal-event_gccal_".$calendar->id;
	$color = GCalendarUtil::getFadedColor($calendar->color);
	$document->addStyleDeclaration(".".$cssClass.",.fc-agenda ".$cssClass." .fc-event-time, .".$cssClass." a, .".$cssClass." span{background-color: ".$color." !important; border-color: #".$calendar->color."; color: white;}");
	if(empty($calendarids) || in_array($calendar->id, $calendarids)){
		$value = html_entity_decode(JRoute::_('index.php?option=com_gcalendar&view=jsonfeed&format=raw&gcid='.$calendar->id));
		$calsSources .= "				'".$value."',\n";
	}
}
$calsSources = trim($calsSources, ",\n");
$calsSources .= "	],\n";

$defaultView = 'month';
if($params->get('defaultView', 'month') == 'week')
$defaultView = 'agendaWeek';
else if($params->get('defaultView', 'month') == 'day')
$defaultView = 'agendaDay';

$daysLong = "[";
$daysShort = "[";
$daysMin = "[";
$monthsLong = "[";
$monthsShort = "[";
for ($i=0; $i<7; $i++) {
	$daysLong .= "'".GCalendarUtil::dayToString($i, false)."'";
	$daysShort .= "'".GCalendarUtil::dayToString($i, true)."'";
	$daysMin .= "'".substr(GCalendarUtil::dayToString($i, true), 0, 2)."'";
	if($i < 6){
		$daysLong .= ",";
		$daysShort .= ",";
		$daysMin .= ",";
	}
}
for ($i=1; $i<=12; $i++) {
	$monthsLong .= "'".GCalendarUtil::monthToString($i, false)."'";
	$monthsShort .= "'".GCalendarUtil::monthToString($i, true)."'";
	if($i < 12){
		$monthsLong .= ",";
		$monthsShort .= ",";
	}
}
$daysLong .= "]";
$daysShort .= "]";
$daysMin .= "]";
$monthsLong .= "]";
$monthsShort .= "]";

$calCode = "// <![CDATA[ \n";
$calCode .= "jQuery(document).ready(function(){\n";
$calCode .= "	var today = new Date();\n";
$calCode .= "	var tmpYear = today.getFullYear();\n";
$calCode .= "	var tmpMonth = today.getMonth();\n";
$calCode .= "	var tmpDay = today.getDate();\n";
$calCode .= "	var tmpView = '".$defaultView."';\n";
$calCode .= "	var vars = window.location.hash.replace(/&amp;/gi, \"&\").split(\"&\");\n";
$calCode .= "	for ( var i = 0; i < vars.length; i++ ){\n";
$calCode .= "		if(vars[i].match(\"^#year\"))tmpYear = vars[i].substring(6);\n";
$calCode .= "		if(vars[i].match(\"^month\"))tmpMonth = vars[i].substring(6)-1;\n";
$calCode .= "		if(vars[i].match(\"^day\"))tmpDay = vars[i].substring(4);\n";
$calCode .= "		if(vars[i].match(\"^view\"))tmpView = vars[i].substring(5);\n";
$calCode .= "	}\n";
$calCode .= "	jQuery('#gcalendar_component').fullCalendar({\n";
$calCode .= "		header: {\n";
$calCode .= "			left: 'prev,next today',\n";
$calCode .= "			center: 'title',\n";
$calCode .= "			right: 'month,agendaWeek,agendaDay'\n";
$calCode .= "		},\n";
$calCode .= "		year: tmpYear,\n";
$calCode .= "		month: tmpMonth,\n";
$calCode .= "		date: tmpDay,\n";
$calCode .= "		defaultView: tmpView,\n";
$calCode .= "		editable: false, theme: ".(!empty($theme)?'true':'false').",\n";
$calCode .= "       weekends: ".($params->get('weekend', 1)==1?'true':'false').",\n";
$calCode .= "		titleFormat: { \n";
$calCode .= "			month: '".$params->get('titleformat_month', 'MMMM yyyy')."',\n";
$calCode .= "			week: \"".$params->get('titleformat_week', "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}")."\",\n";
$calCode .= "			day: '".$params->get('titleformat_day', 'dddd, MMM d, yyyy')."'},\n";
$calCode .= "		firstDay: ".$params->get('weekstart', 0).",\n";
$calCode .= "		firstHour: ".$params->get('first_hour', 6).",\n";
$calCode .= "		maxTime: ".$params->get('max_time', 24).",\n";
$calCode .= "		minTime: ".$params->get('min_time', 0).",\n";
$calCode .= "		monthNames: ".$monthsLong.",\n";
$calCode .= "		monthNamesShort: ".$monthsShort.",\n";
$calCode .= "		dayNames: ".$daysLong.",\n";
$calCode .= "		dayNamesShort: ".$daysShort.",\n";
$calCode .= "		timeFormat: { \n";
$calCode .= "			month: '".$params->get('timeformat_month', 'HH:mm')."',\n";
$calCode .= "			week: \"".$params->get('timeformat_week', "HH:mm{ - HH:mm}")."\",\n";
$calCode .= "			day: '".$params->get('timeformat_day', 'HH:mm{ - HH:mm}')."'},\n";
$calCode .= "			columnFormat: { month: 'ddd', week: 'ddd d', day: 'dddd d'},\n";
$calCode .= "			axisFormat: '".$params->get('axisformat', 'HH:mm')."',\n";
$calCode .= "			allDayText: '".JText::_( 'CALENDAR_VIEW_ALL_DAY' )."',\n";
$calCode .= "			buttonText: {\n";
$calCode .= "			prev:     '&nbsp;&#9668;&nbsp;',\n";  // left triangle
$calCode .= "			next:     '&nbsp;&#9658;&nbsp;',\n";  // right triangle
$calCode .= "			prevYear: '&nbsp;&lt;&lt;&nbsp;',\n"; // <<
$calCode .= "			nextYear: '&nbsp;&gt;&gt;&nbsp;',\n"; // >>
$calCode .= "			today:    '".JText::_( 'TOOLBAR_TODAY' )."',\n";
$calCode .= "			month:    '".JText::_( 'VIEW_MONTH' )."',\n";
$calCode .= "			week:     '".JText::_( 'VIEW_WEEK' )."',\n";
$calCode .= "			day:      '".JText::_( 'VIEW_DAY' )."'\n";
$calCode .= "		},\n";
$calCode .= $calsSources;
$calCode .= "		viewDisplay: function(view) {\n";
$calCode .= "			var d = jQuery('#gcalendar_component').fullCalendar('getDate');\n";
$calCode .= "			var newHash = 'year='+d.getFullYear()+'&month='+(d.getMonth()+1)+'&day='+d.getDate()+'&view='+view.name;\n";
$calCode .= "			if(window.location.hash.replace(/&amp;/gi, \"&\") != newHash)\n";
$calCode .= "			window.location.hash = newHash;\n";
$calCode .= "		},\n";
$calCode .= "		eventRender: function(event, element) {\n";
$calCode .= "			if (event.description)\n";
$calCode .= "				jQuery(element).qtip({\n";
$calCode .= "					content: event.description,\n";
$calCode .= "					position: {\n";
$calCode .= "						corner: {\n";
$calCode .= "							target: 'topLeft',\n";
$calCode .= "							tooltip: 'bottomLeft'\n";
$calCode .= "						}\n";
$calCode .= "					},\n";
$calCode .= "					style: { name: 'cream', tip: 'bottomLeft',\n";
$calCode .= "						border: {\n";
$calCode .= "							radius: 5,\n";
$calCode .= "							width: 1\n";
$calCode .= "						}\n";
$calCode .= "					}\n";
$calCode .= "				});\n";
$calCode .= "		},\n";
$calCode .= "		eventClick: function(event) {\n";
if($params->get('show_event_as_popup', 1) == 1){
	$popupWidth = $params->get('popup_width', 650);
	$popupHeight = $params->get('popup_height', 500);
	$calCode .= "		    if (event.url) {\n";
	$calCode .= "		        jQuery('<iframe src=\"'+event.url+'&tmpl=component\" />').dialog({\n";
	$calCode .= "		           width: ".$popupWidth.",\n";
	$calCode .= "		           height: ".$popupHeight.",\n";
	$calCode .= "		           modal: true,\n";
	$calCode .= "		           autoResize: true,\n";
	$calCode .= "		           title: event.title\n";
	$calCode .= "		        }).width(".($popupWidth-20).").height(".($popupHeight-20).");\n";
	$calCode .= "		        return false;}\n";
}
$calCode .= "		},\n";
$calCode .= "		dayClick: function(date, allDay, jsEvent, view) {\n";
$calCode .= "			jQuery('#gcalendar_component').fullCalendar('gotoDate', date).fullCalendar('changeView', 'agendaDay');\n";
$calCode .= "		},\n";
$calCode .= "		loading: function(bool) {\n";
$calCode .= "			if (bool) {\n";
$calCode .= "				jQuery('#gcalendar_component_loading').show();\n";
$calCode .= "			}else{\n";
$calCode .= "				jQuery('#gcalendar_component_loading').hide();\n";
$calCode .= "			}\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$class = !empty($theme)?'ui':'fc';
$calCode .= "	var custom_buttons ='<td style=\"padding-left:10px\">'+\n";
$calCode .= "			'<div class=\"".$class."-state-default ".$class."-corner-left ".$class."-corner-right ".$class."-state-enabled\">'+\n";
$calCode .= "			'<input type=\"hidden\" id=\"gcalendar_component_date_picker\" value=\"\">'+\n";
$calCode .= "			'<a onClick=\"jQuery(\'#gcalendar_component_date_picker\').datepicker(\'show\');\"><span>".JText::_('SHOW_DATEPICKER')."'+\n";
$calCode .= "			'</span></a>'+\n";
$calCode .= "			'</div>'+\n";
$calCode .= "			'</td>';\n";
$calCode .= "	jQuery('div.fc-button-today').parent('td').after(custom_buttons);\n";
$calCode .= "	jQuery(\"#gcalendar_component_date_picker\").datepicker({\n";
$calCode .= "		dateFormat: 'dd-mm-yy',\n";
$calCode .= "		changeYear: true, \n";
//$calCode .= "		showOn: 'button',\n";
//$calCode .= "		buttonImage: 'images/datepicker.png',\n";
//$calCode .= "		buttonImageOnly: true,     \n";
$calCode .= "		dayNames: ".$daysLong.",\n";
$calCode .= "		dayNamesShort: ".$daysShort.",\n";
$calCode .= "		dayNamesMin: ".$daysMin.",\n";
$calCode .= "		monthNames: ".$monthsLong.",\n";
$calCode .= "		monthNamesShort: ".$monthsShort.",\n";
$calCode .= "		onSelect: function(dateText, inst) {\n";
$calCode .= "			var d = jQuery('#gcalendar_component_date_picker').datepicker('getDate');\n";
$calCode .= "			var view = jQuery('#gcalendar_component').fullCalendar('getView').name;\n";
$calCode .= "			jQuery('#gcalendar_component').fullCalendar('gotoDate', d);\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$calCode .= "	jQuery(window).bind( 'hashchange', function(){\n";
$calCode .= "		var today = new Date();\n";
$calCode .= "		var tmpYear = today.getFullYear();\n";
$calCode .= "		var tmpMonth = today.getMonth();\n";
$calCode .= "		var tmpDay = today.getDate();\n";
$calCode .= "		var tmpView = '".$defaultView."';\n";
$calCode .= "		var vars = window.location.hash.replace(/&amp;/gi, \"&\").split(\"&\");\n";
$calCode .= "		for ( var i = 0; i < vars.length; i++ ){\n";
$calCode .= "			if(vars[i].match(\"^#year\"))tmpYear = vars[i].substring(6);\n";
$calCode .= "			if(vars[i].match(\"^month\"))tmpMonth = vars[i].substring(6)-1;\n";
$calCode .= "			if(vars[i].match(\"^day\"))tmpDay = vars[i].substring(4);\n";
$calCode .= "			if(vars[i].match(\"^view\"))tmpView = vars[i].substring(5);\n";
$calCode .= "		}\n";
$calCode .= "		var date = new Date(tmpYear, tmpMonth, tmpDay,0,0,0);\n";
$calCode .= "		var d = jQuery('#gcalendar_component').fullCalendar('getDate');\n";
$calCode .= "		var view = jQuery('#gcalendar_component').fullCalendar('getView');\n";
$calCode .= "		if(date.getFullYear() != d.getFullYear() || date.getMonth() != d.getMonth() || date.getDate() != d.getDate())\n";
$calCode .= "			jQuery('#gcalendar_component').fullCalendar('gotoDate', date);\n";
$calCode .= "		if(view.name != tmpView)\n";
$calCode .= "			jQuery('#gcalendar_component').fullCalendar('changeView', tmpView);\n";
$calCode .= "	});\n";
$calCode .= "});\n";
$calCode .= "// ]]>\n";
$document->addScriptDeclaration($calCode);

echo $params->get( 'textbefore' );
if($params->get('show_selection', 1) == 1){
	$document->addScript(JURI::base(). 'components/com_gcalendar/views/gcalendar/tmpl/gcalendar.js' );
	$calendar_list = '<div id="gc_gcalendar_view_list"><table>';
	foreach($allCalendars as $calendar) {
		$value = html_entity_decode(JRoute::_('index.php?option=com_gcalendar&view=jsonfeed&format=raw&gcid='.$calendar->id));
		$checked = '';
		if(empty($calendarids) || in_array($calendar->id, $calendarids)){
			$checked = 'checked="checked"';
		}

		$calendar_list .="<tr>\n";
		$calendar_list .="<td><input type=\"checkbox\" name=\"".$calendar->calendar_id."\" value=\"".$value."\" ".$checked." onclick=\"updateGCalendarFrame(this)\"/></td>\n";
		$calendar_list .="<td><font color=\"".GCalendarUtil::getFadedColor($calendar->color)."\">".$calendar->name."</font></td></tr>\n";
	}
	$calendar_list .="</table></div>\n";
	echo $calendar_list;
	echo "<div align=\"center\" style=\"text-align:center\">\n";
	echo "<a id=\"gc_gcalendar_view_toggle\" name=\"gc_gcalendar_view_toggle\" href=\"#\">\n";
	echo "<img id=\"gc_gcalendar_view_toggle_status\" name=\"gc_gcalendar_view_toggle_status\" src=\"".JURI::base()."components/com_gcalendar/images/down.png\" alt=\"".JText::_('CALENDAR_LIST')."\" title=\"".JText::_('CALENDAR_LIST')."\"/>\n";
	echo "</a></div>\n";
}

echo "<div id='gcalendar_component_loading' style=\"text-align: center;\"><img src=\"".JURI::base() . "components/com_gcalendar/images/ajax-loader.gif\"  alt=\"loader\" /></div>";
echo "<div id='gcalendar_component'></div><div id='gcalendar_component_popup' style=\"visibility:hidden\" ></div>";
echo $params->get( 'textafter' );
echo "<div style=\"text-align:center;margin-top:10px\" id=\"gcalendar_powered\"><a href=\"http://g4j.laoneo.net\">Powered by GCalendar</a></div>\n";
?>