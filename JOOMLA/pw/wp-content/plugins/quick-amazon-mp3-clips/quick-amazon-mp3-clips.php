<?php
/*
Plugin Name: Quick Amazon MP3 Clips
Plugin URI: http://JoshHighland.com/#
Description:  This plugin lets you easily create an Amazon MP3 Clips widget. The MP3 Clips widget plays album previews and displays the album art. It's as easy as typing [mp3 artist="ARTIST NAME" album="ALBUM NAME"] anywhere in your post. Modeled after amazon-mp3-widget
Author: Josh Highland
Version: 1.0
Author URI: http://JoshHighland.com/
*/

// Chance the constants below to customize this plugin
define("MP3_DEFAULT_WIDTH",		"336");
define("MP3_DEFAULT_HEIGHT",	"280");
define("MP3_DEFAULT_TAG",		"notpopularcom-20");
define("MP3_DEFAULT_SHUFFLE_TRACKS", "False");
define("MP3_DEFAULT_MARKETPLACE", "US");

// [mp3]
function mp3_func($atts) 
{
	extract(shortcode_atts(array(
		'amazonID' => MP3_DEFAULT_TAG,
		'width' => MP3_DEFAULT_WIDTH,
		'height' => MP3_DEFAULT_HEIGHT,
		'title' => '',
		'shuffle_tracks' => MP3_DEFAULT_SHUFFLE_TRACKS,
		'marketplace' => MP3_DEFAULT_MARKETPLACE,
		'artist' => '',
		'album' => '',
		'songtitle' => ''
	), $atts));

	if($artist != "" || $album != "")
	{
		$maxResults = '';
	
		if($songtitle != "")
		{
			$searchStr = "$artist - $songtitle - $album";
			$maxResults='1';
		}
		else
		{
			$searchStr = "$artist - $album";
		}
	
		$script = "<script>".
		" var amzn_wdgt={widget:'MP3Clips'};".
		" amzn_wdgt.tag='{$tag}';".
		" amzn_wdgt.widgetType='SearchAndAdd';".
		" amzn_wdgt.title='{$title}';".
		" amzn_wdgt.width='{$width}';".
		" amzn_wdgt.height='{$height}';".
		" amzn_wdgt.keywords='{$searchStr}';".
		" amzn_wdgt.shuffleTracks='{$shuffle_tracks}';".
		" amzn_wdgt.marketPlace='US';".
		" amzn_wdgt.maxResults='{$maxResults}';".
		
		" </script>".
		"<script type='text/javascript' src='http://wms.assoc-amazon.com/20070822/US/js/swfobject_1_5.js'>".
		"</script>";
		
		return $script;
	}
	else
	{
		return NULL;
	}
}

add_shortcode('mp3', 'mp3_func');

?>
