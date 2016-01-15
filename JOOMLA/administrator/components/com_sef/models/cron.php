<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

define('COM_SEF_CRON_PERIOD_HOURLY', 1);
define('COM_SEF_CRON_PERIOD_DAILY', 2);
define('COM_SEF_CRON_PERIOD_WEEKLY', 3);
define('COM_SEF_CRON_PERIOD_MONTHLY', 4);
define('COM_SEF_CRON_PERIOD_YEARLY', 5);

define('COM_SEF_CRON_UPDATE_URLS', 1);
define('COM_SEF_CRON_UPDATE_META', 2);
define('COM_SEF_CRON_UPDATE_SITEMAP', 4);
define('COM_SEF_CRON_CRAWL_WEB', 8);

class SEFModelCron extends JModel
{
}