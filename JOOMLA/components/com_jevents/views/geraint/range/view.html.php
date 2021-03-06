<?php
/**
 * JEvents Component for Joomla 1.5.x
 *
 * @version     $Id: view.html.php 941 2010-05-20 13:21:57Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C) 2008-2009 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class for the component frontend
 *
 * @static
 */
class GeraintRange extends JEventsGeraintView 
{
	
	function listevents($tpl = null)
	{
		JEVHelper::componentStylesheet($this);

		$params =& JComponentHelper::getParams( JEV_COM_COMPONENT );

		list($startdate, $enddate) = $this->getStartEndDates();

		list($startyear,$startmonth,$startday)=explode("-",$startdate);
		list($endyear,$endmonth,$endday)=explode("-",$enddate);
		
		$this->assign("startdate",$startdate);
		$this->assign("startyear",$startyear);
		$this->assign("startmonth",$startmonth);
		$this->assign("startday",$startday);
		$this->assign("enddate",$enddate);
		$this->assign("endyear",$endyear);
		$this->assign("endmonth",$endmonth);
		$this->assign("endday",$endday);

		// Note that using a $limit value of -1 the limit is ignored in the query
		$this->assign("data",$this->datamodel->getRangeData($startdate,$enddate,$this->limit, $this->limitstart));

	}
}
