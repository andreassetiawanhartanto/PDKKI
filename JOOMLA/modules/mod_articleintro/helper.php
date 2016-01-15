<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
 
class ModArticleIntro {
    
    public function getArticle($args){
      $db = &JFactory::getDBO();

      $article_id = $args['article_id'];
      $show_title = $args['show_title'];
      
      $query  = "select introtext from #__content ";
      $query .= "where id = ".$article_id;
      $query .= " and state = 1";

      $db->setQuery($query);
      $items = ($items = $db->loadObjectList())?$items:array();
      return $items;
    }
}
