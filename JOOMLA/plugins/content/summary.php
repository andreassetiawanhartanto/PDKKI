<?php
/*******************************************************************************
 * Summary plugin for Joomla CMS
 *
 * @copyright moleculargeek.net
 *       Author`s email: 	molgyk@moleculargeek.net
 *       Author`s homepage : 	http://www.moleculargeek.net/
 *
 * @license GNU/GPL
 *******************************************************************************/

// Ensure this file is being included by a parent file
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

$mainframe->registerEvent('onPrepareContent', 'summary');

function summary(&$row, &$params, $page = 0) {
	//global $id, $Itemid, $database;

    $plugin =& JPluginHelper::getPlugin('content', 'summary');
    $pluginParams = new JParameter($plugin->params);

	$link = $pluginParams->get('link', false);
	$class = $pluginParams->get('class', '');
    $enabled = $pluginParams->get('enabled', true);

    $view  = JRequest::getCmd('view');

 	// Regular expression to search for
	$regex = '%{summary(.*?)}(.*?){/summary}%i';

	// find all instances of summary text and put in $matches
	$text = preg_split($regex, $row->text, -1, PREG_SPLIT_DELIM_CAPTURE);
	
	// Choose between summary if published or text mode
    //
	// Summary mode
	if (count($text) > 1 && $view != 'article' && $enabled) {
	
		for($i = 0; $i < count($text) - 1; $i += 3) {

			// Get the arguments from the markup
			$args = summary_get_args($text[$i + 1], $botParams);

			// Check whether the summary should be linked to the full text version
			$link = isset($args["link"]) ? $args["link"] : $link;

			// Check whether the link class has to be overriden
			$class = $args["class"] ? $args["class"] : $class;

			$out .= $text[$i + 2];
		}
		if ($link) $out = "<a href='" . JRoute::_("task=view&id=" . $row->id . "&Itemid=$Itemid") . "'" . ($class ? " class='$class'" : "") . ">$out</a>";
		$row->text = $out;

	// If in full text mode
	} elseif (count($text) > 1){
		
		for($i = 0; $i < count($text); $i += 3) {

			// Get the arguments from the markup
			$args = summary_get_args($text[$i + 1], $botParams);
			
			// Check if summary should be included in the full text
			if (!$args["full"])
				$text[$i + 2] = "";
				
			$out .= $text[$i] . $text[$i + 2];
		}
		
		$row->text = $out . $text[count($text) - 1];
	}

	return true;
}

function summary_get_args($args, $params = null) {

	$argsRegex = "/\b(\w+)=(?:\"\s*([^\"]*(?:\\\\\"[^\"]*?)*)\s*\"|'\s*([^']*(?:\\\'[^']*?)*)\s*')/i";
	
	preg_match_all($argsRegex, htmlspecialchars_decode($args, ENT_QUOTES), $parsedArgs, PREG_PATTERN_ORDER);

	if(count($parsedArgs[1])) {
		$ret = array_combine($parsedArgs[1], $parsedArgs[2]);

		if(isset($ret["link"])) $ret["link"] = $ret["link"] == "1" ? true : false;
		if(!isset($ret["class"])) $ret["class"] = "";
		$ret["full"] = (isset($ret["full"]) && $ret["full"] == "1") ? true : false;
	} else $ret = array();

	return $ret;	
}
?>