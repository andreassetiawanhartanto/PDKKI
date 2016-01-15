
<!-- AJ Article Listing Module (v3.2.1) starts here -->
<!-- Developed by AbsoluteJoomla.com (c)2011. All rights reserved -->

<?php
/**
* AJ Article Listing Module
* @package Joomla
* @author Luis Murcia & Stefan Hultman
* @version 3.2.1
* @copyright Copyright (C) Absolute Joomla. All rights reserved.
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
global $mainframe;
require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

$database      = &JFactory::getDBO();
$user	       = &JFactory::getUser();
$document      = &JFactory::getDocument();
$config        = &JFactory::getConfig();
$itemid        = (((int)trim($params->get('linkmenu')))>0) ? trim($params->get('linkmenu')) : 1;
$labelhandler  = $params->get('labelhandler');
$seclabel      = ($labelhandler == 'defaultlabels') ? JText::_('SECTION_DROPDOWN_LABEL') : trim($params->get('seclabel'));
$catlabel      = ($labelhandler == 'defaultlabels') ? JText::_('CATEGORY_DROPDOWN_LABEL'): trim($params->get('catlabel'));
$artlabel      = ($labelhandler == 'defaultlabels') ? JText::_('ARTICLE_DROPDOWN_LABEL') : trim($params->get('artlabel'));
$buttonlabel   = ($labelhandler == 'defaultlabels') ? JText::_('SUBMIT_BUTTON')          : trim($params->get('buttonlabel'));
$message       = ($labelhandler == 'defaultlabels') ? JText::_('JS_ERROR_MESSAGE')       : trim($params->get('message'));
$sortby_sects  = $params->get('sortby_sects');
$article_menu  = ($params->get('article_menu')=='hide') ? 0 : 1;
$sortorder     = $params->get('sortorder');
$layout        = ($params->get('layout') > 0) ? 1 : 0;
$sections_showhide  = $params->get('sections_showhide');
$secids        = ($params->get('secids') != '') ? trim($params->get('secids')) : 0;
$maxwidth      = ($params->get('maxwidth') > 0) ? $params->get('maxwidth') : 120;
$marginsize    = ($params->get('marginsize') > 0) ? $params->get('marginsize') : 3;
$paddingsize   = ($params->get('paddingsize') > 0) ? $params->get('paddingsize') : 2;
$sortby_arts   = $params->get('sortby_arts');
$sortby_cats   = $params->get('sortby_cats');
$article_list  = array();
$category_list = array();
$alignbutton   = $params->get('alignbutton');
$layout_chunks = ($layout == 1) ? '' : '</td></tr><tr align="'.$alignbutton.'"><td>';
$sef_urls      = $config->getValue('config.sef');

$unique_id     = rand(1,100000);


if($sections_showhide == 'hide'){
    $inc_exc = " AND s.id NOT IN (".$secids.") ";
} else if($secids != 0) {
    $inc_exc = " AND s.id IN (".$secids.") ";
} else {
    $inc_exc = "";
}

$sections_qry  = "SELECT s.id AS id, s.title AS title FROM #__sections AS s
                    WHERE s.published=1 ".$inc_exc."
                    ORDER BY ".$sortby_sects." ".$sortorder;

$database->setQuery( $sections_qry );
$sections      = $database->loadObjectList();
if(count($sections)<1){
    echo "<b>Attention:</b><br/>Please check the AJ Article Listing Module parameters and make sure the combination of values will return a valid resultset.";
} else {
    foreach($sections as $key => $item) {
        $sections_id[] = $item->id;
    }

    $cats_qry      = 'SELECT distinct c.id AS id, c.title AS title, c.section AS section,
                        CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as catslug
                        FROM #__categories AS c, #__content AS a
                        WHERE a.catid = c.id AND a.state = 1
                         AND c.published=1 AND c.section IN ('.implode(",",$sections_id).')
                        ORDER BY '.$sortby_cats.' '.$sortorder;

    $database->setQuery( $cats_qry );
    $categories    = $database->loadObjectList();

    if(isset($categories)){
        foreach($categories as $key => $item) {

            if((int)$sef_urls==1){
                $catlist[$key]->href  = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug, $item->section));
            } else {
                $catlist[$key]->href  = ContentHelperRoute::getCategoryRoute($item->catslug, $item->section).(($itemid>1) ? '&Itemid='.$itemid : '');
            }
            $catlist[$key]->title = $item->title;
            $catlist[$key]->id = $item->id;
            $catlist[$key]->section = $item->section;
        }
    }
    $category_list = $catlist;

    if($article_menu == 1){
            $articles_qry  = 'SELECT a.id AS id, a.title AS title, a.catid AS catid, a.sectionid AS sectionid,
                                                    CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,
                                                    CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(":", b.id, b.alias) ELSE b.id END as catslug
                                                    FROM #__content AS a, #__categories AS b
                                                    WHERE a.catid = b.id AND a.state = 1
                                                    AND a.access <= '.(int) $user->get( 'aid' ).'
                                                    ORDER BY '.$sortby_arts.' '.$sortorder;

            $database->setQuery( $articles_qry );
            $articles      = $database->loadObjectList();

            if(isset($articles)){
                    foreach($articles as $key => $item) {
                    	if((int)$sef_urls==1){
                        	$artlist[$key]->href  = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid));
                    	} else {
                        	$artlist[$key]->href  = ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid).(($itemid>1) ? '&Itemid='.$itemid : '');
                    	}
                        $artlist[$key]->title = $item->title;
                        $artlist[$key]->catid = $item->catid;
                        $artlist[$key]->id = $item->id;
                    }
            }
            $article_list = $artlist;
    }
    ?>
    <script language="javascript">

            var AJ_ARTICLEMENU<?php echo $unique_id ?> = <?php echo $article_menu; ?>;

        function AJRemove<?php echo $unique_id ?>(t_id){
            for (var i= (t_id.options.length - 1); i >0 ; i--) {
                t_id.remove(i);
            }
        }

        function AJConstructMenu<?php echo $unique_id ?>(t_menu, t_id, switch_what) {
            if(switch_what == 'section'){
                for (var i=0; i<AJ_CATEGORIES<?php echo $unique_id ?>.length; i++) {
                    if (AJ_CATEGORIES<?php echo $unique_id ?>[i][0] == t_id){
                        var cOpt   = document.createElement("option");
                        cOpt.value = AJ_CATEGORIES<?php echo $unique_id ?>[i][2];
                        cOpt.text  = AJ_CATEGORIES<?php echo $unique_id ?>[i][3];
                        t_menu.options.add(cOpt);
                    }
                }
            } else {
                for (var i=0; i<AJ_ARTICLES<?php echo $unique_id ?>.length; i++) {
                    if (AJ_ARTICLES<?php echo $unique_id ?>[i][0] == t_id){
                        var cOpt   = document.createElement("option");
                        cOpt.value = AJ_ARTICLES<?php echo $unique_id ?>[i][2];
                        cOpt.text  = AJ_ARTICLES<?php echo $unique_id ?>[i][3];
                        t_menu.options.add(cOpt);
                    }
                }
            }
        }

        function AJSwitch<?php echo $unique_id ?>(switch_what){
            var t_section  = document.getElementById('aj_section<?php echo $unique_id ?>');
            var t_category = document.getElementById('aj_category<?php echo $unique_id ?>');
            if(AJ_ARTICLEMENU<?php echo $unique_id ?> == 1){
                            var t_article  =  document.getElementById('aj_article<?php echo $unique_id ?>');
                            AJRemove<?php echo $unique_id ?>(t_article);
                    }
            if(switch_what == 'section'){
                AJRemove<?php echo $unique_id ?>(t_category);
                if(t_section.value > 0){
                    AJConstructMenu<?php echo $unique_id ?>(t_category,t_section.value, switch_what);
                }
            } else {
                if(t_category.value > 0){

                                    AJConstructMenu<?php echo $unique_id ?>(t_article,t_category.value, switch_what);
                }
            }
        }

        function AJGo2<?php echo $unique_id ?>(){
            var t_section  = document.getElementById('aj_section<?php echo $unique_id ?>');
            var t_category = document.getElementById('aj_category<?php echo $unique_id ?>');
                    var t_article  =  (AJ_ARTICLEMENU<?php echo $unique_id ?> != 1) ? 0 : document.getElementById('aj_article<?php echo $unique_id ?>').value;
            if ((t_section.value == 0) || (t_category.value == 0)){
                alert('<?php echo $message; ?>');
            } else {
                if(AJ_ARTICLEMENU<?php echo $unique_id ?> == 1 && (t_article > 0)){
                                    for (var i=0; i<AJ_ARTICLES<?php echo $unique_id ?>.length; i++){
                                            if(AJ_ARTICLES<?php echo $unique_id ?>[i][2]==t_article){
                                                    t_link = AJ_ARTICLES<?php echo $unique_id ?>[i][1];
                                                    break;
                                            }
                                    }
                } else {
                    for (var i=0; i<AJ_CATEGORIES<?php echo $unique_id ?>.length; i++){
                        if(AJ_CATEGORIES<?php echo $unique_id ?>[i][2]==t_category.value){
                            t_link = AJ_CATEGORIES<?php echo $unique_id ?>[i][1];
                            break;
                        }
                    }
                }
                window.location.href = t_link;
            }
        }

        var AJ_ARTICLES<?php echo $unique_id ?>   = new Array();
        var AJ_CATEGORIES<?php echo $unique_id ?> = new Array();
    <?php
        $i = 0;
        foreach ($category_list as $cat) {
            echo "AJ_CATEGORIES".$unique_id."[".$i."] = new Array(".$cat->section.", '".$cat->href."', ".$cat->id.", '".addslashes($cat->title)."'); \n";
            $i++;
        }
            if($article_menu == 1){
                    $i = 0;
                    foreach ($article_list as $item) {
                            echo "AJ_ARTICLES".$unique_id."[".$i."] = new Array(".$item->catid.", '".$item->href."', ".$item->id.", '".addslashes($item->title)."'); \n";
                            $i++;
                    }
            }
    ?>
    </script>
    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <select id="aj_section<?php echo $unique_id ?>" onchange="AJSwitch<?php echo $unique_id ?>('section');" style="width:<?php echo $maxwidth; ?>px;margin:<?php echo $marginsize; ?>px;padding:<?php echo $paddingsize; ?>px;">
                <option value="0"><?php echo $seclabel; ?></option>
    <?php
        foreach ($sections as $item) {
            echo "<option value='".$item->id."'>".$item->title."</option> \n";
        }
    ?>
                </select>
    <?php echo $layout_chunks; ?>
                <select id="aj_category<?php echo $unique_id ?>" onchange="AJSwitch<?php echo $unique_id ?>('category');" style="width:<?php echo $maxwidth; ?>px;margin:<?php echo $marginsize; ?>px;padding:<?php echo $paddingsize; ?>px;">
                    <option value="0"><?php echo $catlabel; ?></option>
                </select>
    <?php
            if($article_menu == 1){
                    echo $layout_chunks;
    ?>
                <select id="aj_article<?php echo $unique_id ?>" style="width:<?php echo $maxwidth; ?>px;margin:<?php echo $marginsize; ?>px;padding:<?php echo $paddingsize; ?>px;">
                    <option value="0"><?php echo $artlabel; ?></option>
                </select>
    <?php
            }
            echo $layout_chunks;
    ?>
                <input type="button" name="go" value="<?php echo $buttonlabel; ?>" onclick="AJGo2<?php echo $unique_id ?>();" style="margin:<?php echo $marginsize; ?>px;padding:<?php echo $paddingsize; ?>px;"/>
            </td>
        </tr>
    </table>

<?php
}
?>

<!-- AJ Article Listing Module (v3.2.1) ends here -->

