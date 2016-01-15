<?php
/*
 * Created on 24.10.2011
 */
 
defined('_JEXEC') or die('Restricted access');

class JHTMLStatistic
{
	static function link($url,$text,$attribs=null) {
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
        }

		return "<a href='".$url."' ".$attribs.">".$text."</a>";
	}
}
?>
