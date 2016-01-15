<?php


defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');
jckimport('ckeditor.htmlwriter.javascript');


class plgEditorSession extends JPlugin 
{
		
  	function plgEditorSession(& $subject, $config) 
	{
		parent::__construct($subject, $config);
	}

	function afterLoad(&$params)
	{
		
		$mainframe = JFactory::getApplication();
			
		//lets create JS object
		$javascript = new JCKJavascript();
		
        $user = JFactory::getUser();
        $username = $user->get('username');
		$email =  $user->get('email');
        $clientid = $mainframe->getClientId(); 
        
        $cacheKey = md5('jckeditor'.$username.$email.$clientid);
        
		$javascript->addScriptDeclaration("
			editor.config['client'] = '" .  $cacheKey . "';");

		return $javascript->toRaw();
		
		
	}

}