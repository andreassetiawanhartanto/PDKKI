<?php
/**
 * @author		Sami Elkady
 * @copyright	Copyright (C) 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL.
 * @System      Sami Router Plugin for Joomla!
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// import the JPlugin class
jimport( 'joomla.plugin.plugin' );

class plgSystemSamiurl extends JPlugin{
	
	//  actions after initialisation
	public function onAfterInitialise()	{
		$app =& JFactory::getApplication();

		if($app->getName() != 'site')
		{
			return true;
		}
		else
			$this->route(); 
	}
	//Perform actions after rendering
	public function onAfterRender()	{
		$app =& JFactory::getApplication();

		//sef is not enabled, Don't do any thing
		if( intval( $app->getCfg( 'sef' ) ) == 0 )
			return true;
		if($app->getName() == 'site')
			$this->replaceURL(); 
		else
			return true;
	}
	private function route(){
		$current = str_replace( JURI::base(), '', JURI::current() );
		$pos = strpos('#'.$current, 'article/'); 
		if($pos){
			$id = (int) str_replace("article/", "", $current);
			JRequest::setVar( 'option', 'com_content', 'get' );
			JRequest::setVar( 'view', 'article', 'get' );
			JRequest::setVar( 'id', $id, 'get' );
			return true;			
		}else{
			return true;
		}
	}
	private function replaceURL(){
		$app =& JFactory::getApplication();

		if($app->getName() != 'site') {
			return true;
		}
		$base   = JURI::base(true).'/';
		$buffer = JResponse::getBody();
		//get cat aliace and ID
		$buffer=str_replace("component/content/article/", "article/", $buffer);
		$regex ="/article\/[a-zA-Z0-9 ._-]+\//";
		$buffer=preg_replace($regex, "article/", $buffer);

		

		JResponse::setBody($buffer);

		return true;
	}
}