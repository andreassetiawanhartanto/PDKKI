<?php
/**
 * Main File
 *
 * @package     Articles Anywhere
 * @version     1.13.1
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2011 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die();

if (
		JRequest::getCmd( 'disable_articlesanywhere' )
	||	JRequest::getCmd( 'format' ) == 'raw'
	||	JRequest::getCmd( 'option' ) == 'com_joomfishplus'
	||	JRequest::getInt( 'nn_qp' )
) {
	return;
}

require_once dirname( __FILE__ ).'/articlesanywhere/articlesanywhere.php';