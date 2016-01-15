<?php
/*
/**
* CHRONOFORMS version 3.0 
* Copyright (c) 2008 Chrono_Man, ChronoEngine.com. All rights reserved.
* Author: Chrono_Man (ChronoEngine.com)
* @license		GNU/GPL
* See readme.html.
* Visit http://www.ChronoEngine.com for regular update and information.
**/
?>
<?php
	$post = $posted;
	$skippedarray = explode(",", $MyForm->formparams('captcha_dataload_skip', ''));
	ob_start();
	eval( "?>".$MyForm->formrow->html);
	$MyForm->formrow->html = ob_get_clean();
	//get all fields names
	preg_match_all('/name=("|\')([^(>|"|\')]*?)("|\')/i', $MyForm->formrow->html, $fieldsnamesmatches);
	$allfieldsnames = array();
	foreach ( $fieldsnamesmatches[2] as $fieldsnamesmatche ) {
		if ( strpos($fieldsnamesmatche, '[]') ) {
			$fieldsnamesmatche = str_replace('[]', '', $fieldsnamesmatche);
		}
		$allfieldsnames[] = trim($fieldsnamesmatche);
	}
	$allfieldsnames = array_unique($allfieldsnames);
	//print_r($allfieldsnames );
	foreach($allfieldsnames as $allfieldsname){
		if(!isset($post[$allfieldsname])){
			$post[$allfieldsname] = '';
		}else{
			if(is_array($post[$allfieldsname])){
				$post[$allfieldsname] = implode(', ', $post[$allfieldsname]);
			}
			$post[$allfieldsname] = htmlentities($post[$allfieldsname], ENT_QUOTES, 'UTF-8');
		}
	}
	//end fields names
	if ( count($post) ) {
		//text fields
		$pattern_input = '/<input([^>]*?)type=("|\')(text|password)("|\')([^>]*?)>/is';
		$matches = array();
		preg_match_all($pattern_input, $MyForm->formrow->html, $matches);
		foreach ( $matches[0] as $match ) {
			$pattern_value = '/value=("|\')(.*?)("|\')/i';
			$pattern_name = '/name=("|\')(.*?)("|\')/i';
			preg_match($pattern_name, $match, $matches_name);
			if(!in_array($matches_name[2], $skippedarray)){
				$valuematch = preg_replace($pattern_value, '', $match);
				$namematch = preg_replace($pattern_name, 'name="${2}" value="<?php echo $post[\'${2}\']; ?>"', $valuematch);
				$MyForm->formrow->html = str_replace($match, $namematch, $MyForm->formrow->html);
			}
		}
		//hidden fields
		$pattern_input = '/<input([^>]*?)type=("|\')hidden("|\')([^>]*?)>/is';
		$matches = array();
		preg_match_all($pattern_input, $MyForm->formrow->html, $matches);
		foreach ($matches[0] as $match) {
			$pattern_value = '/value=("|\')(.*?)("|\')/i';
			$pattern_name = '/name=("|\')(.*?)("|\')/i';
			preg_match($pattern_name, $match, $matches_name);
			if(!in_array($matches_name[2], $skippedarray)){
				$valuematch = preg_replace($pattern_value, '', $match);
				$namematch = preg_replace($pattern_name, 'name="${2}" value="<?php echo $post[\'${2}\']; ?>"', $valuematch);
				$MyForm->formrow->html = str_replace($match, $namematch, $MyForm->formrow->html);
			}
		}
		//checkboxes or radios fields
		$pattern_input = '/<input([^>]*?)type=("|\')(checkbox|radio)("|\')([^>]*?)>/is';
		$matches = array();
		preg_match_all($pattern_input, $MyForm->formrow->html, $matches);
		foreach ($matches[0] as $match) {
			$pattern_value = '/value=("|\')(.*?)("|\')/i';
			$pattern_name = '/name=("|\')(.*?)("|\')/i';
			preg_match($pattern_name, $match, $matches_name);
			preg_match($pattern_value, $match, $matches_value);
			if ( !in_array(str_replace('[]', '', $matches_name[2]), $skippedarray) ) {
				//multi values
				if ( strpos($matches_name[2], '[]') ) {
					$namematch = preg_replace(CFChronoForm::cfskipregex($pattern_name), 'name="${2}" <?php if(in_array("'.$matches_value[2].'", explode(", ", $post["'.str_replace('[]', '', $matches_name[2]).'"])))echo \'checked="checked"\'; ?>', $match);
				//single values
				} else {
					$namematch = preg_replace($pattern_name, 'name="${2}" <?php if($post["'.$matches_name[2].'"] == "'.$matches_value[2].'")echo \'checked="checked"\'; ?>', $match);
				}
				$MyForm->formrow->html = str_replace($match, $namematch, $MyForm->formrow->html);
			}
		}
		//textarea fields
		$pattern_textarea = '/<textarea([^>]*?)>(.*?)<\/textarea>/is';
		$matches = array();
		preg_match_all($pattern_textarea, $MyForm->formrow->html, $matches);
		$namematch = '';
		foreach ( $matches[0] as $match ) {
			$pattern_value = '/value=("|\')(.*?)("|\')/i';
			$pattern_name = '/name=("|\')(.*?)("|\')/i';
			preg_match($pattern_name, $match, $matches_name);
			if(!in_array($matches_name[2], $skippedarray)){
				$pattern_textarea2 = '/(<textarea(.*?)>)(.*?)(<\/textarea>)/is';
				$newtextarea_match = preg_replace($pattern_textarea2, '${1}<?php echo $post[\''.$matches_name[2].'\']; ?>${4}', $match);
				$MyForm->formrow->html = str_replace($match, $newtextarea_match, $MyForm->formrow->html);
			}
		}
		//select boxes
		$pattern_select = '/<select(.*?)select>/is';
		$matches = array();
		preg_match_all($pattern_select, $MyForm->formrow->html, $matches);

		foreach ($matches[0] as $match) {
			$selectmatch = $match;
			$pattern_select2 = '/<select([^>]*?)>/is';
			preg_match_all($pattern_select2, $match, $matches2);
			$options = preg_replace(array('/'.CFChronoForm::cfskipregex($matches2[0][0]).'/is', '/<\/select>/i'), array('', ''), $match);

			$pattern_name = '/name=("|\')(.*?)("|\')/i';
			preg_match($pattern_name, $matches2[0][0], $matches_name);
			if(!in_array(str_replace('[]', '', $matches_name[2]), $skippedarray)){
				//multi select
				if(strpos($matches_name[2], '[]')){
					$pattern_options = '/<option(.*?)<\/option>/is';
					preg_match_all($pattern_options, $options, $matches_options);
					foreach($matches_options[0] as $matches_option){
						$pattern_value = '/value=("|\')(.*?)("|\')/i';
						preg_match($pattern_value, $matches_option, $matches_value);
						$optionmatch = preg_replace('/<option/i', '<option <?php if(in_array("'.$matches_value[2].'", explode(", ", $post["'.str_replace('[]', '', $matches_name[2]).'"])))echo \'selected="selected"\'; ?>', $matches_option);
						$selectmatch = str_replace($matches_option, $optionmatch, $selectmatch);
					}
				//single select
				} else {
					$pattern_options = '/<option(.*?)<\/option>/is';
					preg_match_all($pattern_options, $options, $matches_options);
					foreach($matches_options[0] as $matches_option){
						$pattern_value = '/value=("|\')(.*?)("|\')/i';
						preg_match($pattern_value, $matches_option, $matches_value);
						$optionmatch = preg_replace('/<option/i', '<option <?php if($post["'.$matches_name[2].'"] == "'.$matches_value[2].'")echo \'selected="selected"\'; ?>', $matches_option);
						$selectmatch = str_replace($matches_option, $optionmatch, $selectmatch);
					}
				}
				$MyForm->formrow->html = str_replace($match, $selectmatch, $MyForm->formrow->html);
			}
		}
	}			
?>