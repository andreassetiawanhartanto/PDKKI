<?php
/**
 * @plugin		Image resizer plugin for Chronoforms
 * @version		1.0
 * @author		Emmanuel Danan - www.vistamedia.fr - emmanuel AT vistamedia DOT fr
 * @copyright	Copyright (C) 2008 Vistamedia. All rights reserved.
 * @license		GNU/GPL
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
defined('_JEXEC') or die('Restricted access');
global $mainframe;
require_once( $mainframe->getPath( 'class', 'com_chronocontact' ) );

// the class name must be the same as the file name without the .php at the end
class cf_image_resize
{
    //the next 3 fields must be defined for every plugin
    var $result_TITLE = "Image resize and thumnail";
    var $result_TOOLTIP = "Permet de redimensionner une image";
    var $plugin_name = "cf_image_resize"; 		// must be the same as the class name
    var $event = "ONSUBMIT"; 					// must be defined and in Uppercase, should be ONSUBMIT or ONLOAD

    // the next function must exist and will have the backend config code
    function show_conf($row, $id, $form_id, $option)
    {
        global $mainframe;

    	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'.DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();

    	// identify and initialise the parameters used in this plugin
        $params_array = array(
        	'photo' => 'file_0',
        	'delete_original' => '0',
			'quality' => '90',
			'big_directory' => '',
			'big_image_prefix' => '',
        	'big_image_suffix' => '_big',
			'big_image_height' => '300',
			'big_image_width' => '400',
			'big_image_r' => '255',
			'big_image_g' => '255',
			'big_image_b' => '255',
        	'big_image_method' => '0',
        	'med_directory' => '',
        	'med_image_use' => '0',
			'med_image_prefix' => '',
        	'med_image_suffix' => '_med',
			'med_image_height' => '300',
			'med_image_width' => '400',
			'med_image_r' => '255',
			'med_image_g' => '255',
			'med_image_b' => '255',
        	'med_image_method' => '0',
            'small_image_use' => '0',
        	'small_directory' => '',
			'small_image_prefix' => '',
        	'small_image_suffix' => '_small',
			'small_image_height' => '300',
			'small_image_width' => '400',
			'small_image_r' => '255',
			'small_image_g' => '255',
			'small_image_b' => '255',
			'small_image_method' => '0'
			);
        $params = $helper->loadParams($row, $params_array);
        //echo '<div>$params: '.print_r($params, true).'</div>';

    	$copyright = "<div style='margin-top:6px; border-top:solid 1px silver; padding:6px;'>
    	Image resizer plugin for chronoforms - &copy;2008 Emmanuel Danan
    	<a href='http://www.vistamedia.fr'
title='Joomla! expert in Aix en Provence - France' target='_blank'>www.vistamedia.fr</a>
: <a href='http://www.joomla.fr' title='Joomla! french support portal' target='_blank'>www.joomla.fr</a></div>";
    	$copyright = $helper->wrapTD($copyright, array('colspan' => '4' ));
    	$copyright = $helper->wrapTR($copyright, array('class' => 'cf_config'));
?>
<form action="index2.php" method="post" name="adminForm" id="adminForm" class="adminForm">
<?php
    	echo $pane->startPane("image_resize");
    	echo $pane->startPanel( 'General', "configure" );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('General setup', '', true, $attribs['header']);
        echo $helper->wrapTR($input);

        $tooltip = "Photo Field: The name of the photo upload field in your form";
        $input = $helper->createInputTD('Photo Field', 'params[photo]',
            $params->get('photo'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Delete orginal image: Delete the original image from the upload directory";
        $input = $helper->createYesNoTD("Delete orginal image", 'params[delete_original]', '',
            $params->get('delete_original'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));


        $tooltip = "Image quality: set the quality of ouput jpg images.";
        $q_range = array();
        foreach ( range(100, 5, -5) as $v ) {
            $q_range[$v] = JHTML::_('select.option', $v, $v);
        }
        $input = $helper->createSelectTD('Image quality % (jpg only)', 'params[quality]', $q_range, $params->get('quality'),
           	$attribs['select'], $tooltip );
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        echo $copyright;
?>
</table>
<?php
    echo $pane->endPanel();
    echo $pane->startPanel( 'Large Image', 'large' );
?>
    <table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('Settings for the large image', '', true, $attribs['header']);
        echo $helper->wrapTR($input);

        $tooltip = "Directory where the file will be stored. Don‘t forget the slash at the end ;-) e.g. images/stories/<br />
        	If you leave this empty it will default to the Form file uploads folder.";
        $input = $helper->createInputTD('Directory', 'params[big_directory]',
            $params->get('big_directory'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "The prefix for the created image name e.g. big_";
        $input = $helper->createInputTD('Image prefix', 'params[big_image_prefix]',
            $params->get('big_image_prefix'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "The suffix for the created image name e.g. _big";
        $input = $helper->createInputTD('Image suffix', 'params[big_image_suffix]',
            $params->get('big_image_suffix'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Dimensions of the re-sized image in pixels";
        $input  = 'Height: ';
        $input .= $helper->createInput('params[big_image_height]',
            $params->get('big_image_height'),
            array('maxlength' => '5', 'size' => '5', 'class' => 'text_area' ), false, false);
        $input .= 'px&nbsp;&nbsp;&nbsp;Width: ';
        $input .= $helper->createInput('params[big_image_width]',
            $params->get('big_image_width'),
            array('maxlength' => '5', 'size' => '5', 'class' => 'text_area' ), false, false);
        $input .= 'px';
        $input = $helper->createTextTD('Dimensions', $input, $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Alpha channel for png transparency. RGB color of the background ;-)";
        $input  = 'R: ';
        $input .= $helper->createInput('params[big_image_r]',
            $params->get('big_image_r'),
            array('maxlength' => '3', 'size' => '3', 'class' => 'text_area' ), false, false);
        $input .= '&nbsp;&nbsp;&nbsp;G: ';
        $input .= $helper->createInput('params[big_image_g]',
            $params->get('big_image_g'),
            array('maxlength' => '3', 'size' => '3', 'class' => 'text_area' ), false, false);
        $input .= '&nbsp;&nbsp;&nbsp;B: ';
        $input .= $helper->createInput('params[big_image_b]',
            $params->get('big_image_b'),
            array('maxlength' => '3', 'size' => '3', 'class' => 'text_area' ), false, false);
        $input .= ' 0-255';
        $input = $helper->createTextTD('Alpha channel', $input, $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "The way your images will be generated (either scale or crop)";
        $input = $helper->createRadioTD('Processing method', 'params[big_image_method]',
            array('0' => 'Scale', '1' => 'Crop'),  $params->get('big_image_method'),
        $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        echo $copyright;
?>
</table>
<?php
    echo $pane->endPanel();
    echo $pane->startPanel( 'Medium Image', 'medium' );
?>
    <table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('Settings for the medium image', '', true, $attribs['header']);
        echo $helper->wrapTR($input);

        $tooltip = "Create a medium sized image?.";
        $input = $helper->createYesNoTD("Create medium image?", 'params[med_image_use]', '',
            $params->get('med_image_use'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Directory where the file will be stored. Don‘t forget the slash at the end ;-) e.g. images/stories/<br />
        	If you leave this empty it will default to the Form file uploads folder.";
        $input = $helper->createInputTD('Directory', 'params[med_directory]',
            $params->get('med_directory'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "The prefix for the created image name e.g. med_";
        $input = $helper->createInputTD('Image prefix', 'params[med_image_prefix]',
            $params->get('med_image_prefix'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "The suffix for the created image name e.g. _med";
        $input = $helper->createInputTD('Image suffix', 'params[med_image_suffix]',
            $params->get('med_image_suffix'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Dimensions of the re-sized image in pixels";
        $input  = 'Height: ';
        $input .= $helper->createInput('params[med_image_height]',
            $params->get('med_image_height'),
            array('maxlength' => '5', 'size' => '5', 'class' => 'text_area' ), false, false);
        $input .= 'px&nbsp;&nbsp;&nbsp;Width: ';
        $input .= $helper->createInput('params[med_image_width]',
            $params->get('med_image_width'),
            array('maxlength' => '5', 'size' => '5', 'class' => 'text_area' ), false, false);
        $input .= 'px';
        $input = $helper->createTextTD('Dimensions', $input, $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Alpha channel for png transparency. RGB color of the background ;-)";
        $input  = 'R: ';
        $input .= $helper->createInput('params[med_image_r]',
            $params->get('med_image_r'),
            array('maxlength' => '3', 'size' => '3', 'class' => 'text_area' ), false, false);
        $input .= '&nbsp;&nbsp;&nbsp;G: ';
        $input .= $helper->createInput('params[med_image_g]',
            $params->get('med_image_g'),
            array('maxlength' => '3', 'size' => '3', 'class' => 'text_area' ), false, false);
        $input .= '&nbsp;&nbsp;&nbsp;B: ';
        $input .= $helper->createInput('params[med_image_b]',
            $params->get('med_image_b'),
            array('maxlength' => '3', 'size' => '3', 'class' => 'text_area' ), false, false);
        $input .= ' 0-255';
        $input = $helper->createTextTD('Alpha channel', $input, $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "The way your images will be generated (either scale or crop)";
        $input = $helper->createRadioTD('Processing method', 'params[med_image_method]',
            array('0' => 'Scale', '1' => 'Crop'),  $params->get('med_image_method'),
        $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        echo $copyright;
?>
</table>
<?php
    echo $pane->endPanel();
    echo $pane->startPanel( 'Small Image', 'small' );
?>
    <table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('Settings for the small image', '', true, $attribs['header']);
        echo $helper->wrapTR($input);

        $tooltip = "Create a small sized image?.";
        $input = $helper->createYesNoTD("Create small image?", 'params[small_image_use]', '',
            $params->get('small_image_use'), '', $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Directory where the file will be stored. Don‘t forget the slash at the end ;-) e.g. images/stories/<br />
        	If you leave this empty it will default to the Form file uploads folder.";
        $input = $helper->createInputTD('Directory', 'params[small_directory]',
            $params->get('small_directory'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "The prefix for the created image name e.g. small_";
        $input = $helper->createInputTD('Image prefix', 'params[small_image_prefix]',
            $params->get('small_image_prefix'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "The suffix for the created image name e.g. _small";
        $input = $helper->createInputTD('Image suffix', 'params[small_image_suffix]',
            $params->get('small_image_suffix'), '', $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Dimensions of the re-sized image in pixels";
        $input  = 'Height: ';
        $input .= $helper->createInput('params[small_image_height]',
            $params->get('small_image_height'),
            array('maxlength' => '5', 'size' => '5', 'class' => 'text_area' ), false, false);
        $input .= 'px&nbsp;&nbsp;&nbsp;Width: ';
        $input .= $helper->createInput('params[small_image_width]',
            $params->get('small_image_width'),
            array('maxlength' => '5', 'size' => '5', 'class' => 'text_area' ), false, false);
        $input .= 'px';
        $input = $helper->createTextTD('Dimensions', $input, $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "Alpha channel for png transparency. RGB color of the background ;-)";
        $input  = 'R: ';
        $input .= $helper->createInput('params[small_image_r]',
            $params->get('small_image_r'),
            array('maxlength' => '3', 'size' => '3', 'class' => 'text_area' ), false, false);
        $input .= '&nbsp;&nbsp;&nbsp;G: ';
        $input .= $helper->createInput('params[small_image_g]',
            $params->get('small_image_g'),
            array('maxlength' => '3', 'size' => '3', 'class' => 'text_area' ), false, false);
        $input .= '&nbsp;&nbsp;&nbsp;B: ';
        $input .= $helper->createInput('params[small_image_b]',
            $params->get('small_image_b'),
            array('maxlength' => '3', 'size' => '3', 'class' => 'text_area' ), false, false);
        $input .= ' 0-255';
        $input = $helper->createTextTD('Alpha channel', $input, $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        $tooltip = "The way your images will be generated (either scale or crop)";
        $input = $helper->createRadioTD('Processing method', 'params[small_image_method]',
            array('0' => 'Scale', '1' => 'Crop'),  $params->get('small_image_method'),
        $attribs['input'], $tooltip);
        echo $helper->wrapTR($input, array('class' => 'cf_config'));

        echo $copyright;
?>
</table>
<?php
         echo $pane->endPanel();
         echo $pane->startPanel( 'Help', 'help' );
?>
<table border="0" cellpadding="3" cellspacing="0" class='cf_table' >
<?php
        $input = $helper->createHeaderTD('How to configure the Image-Re-sizer plugin', '',
            true, array('colspan' => '4', 'class' => 'cf_header'));
        echo $helper->wrapTR($input);
?>
    <tr>
        <td colspan='4' style='border:1px solid silver; padding:6px;'>
        <div>The Image Re-sizer plugin will take a file (jpg, png, or gif) uploaded from your form and re-size it
        either by scaling or cropping. It will also optionally create medium and small versions of the image.
        You can specify the image sizes and the storage folder.</div>
        <ul><li>On the General tab you can identify the upload file name (only a single upload field is supported).
        You can also choose to delete the original file;
        and set the quality for jpg conversions (higher quality uses larger files).</li>
        <li>The Large, Medium & Small tabs are identical except
        that you can turn the Medium and Small copies on and off.</li>
        <li>For each size you can set an upload directory; an image prefix for the files and the
        size of the new image in pixels. The alpha channel sets the background color for png files only.
        The processing method allows to to decide if images should be scaled when as much of the image will be kept as possible,
        or cropped when the center area will be kept and the edges sliced off to reach the new size.</li>
        <li>Note that although the images are called 'Large, Medium and Small' you can choose any size in each tab.</li>
        </ul>
        </td>
    </tr>
<?php
        echo $copyright;
?>
    </table>
<?php
        echo $pane->endPanel();
        echo $pane->endPane();

        $hidden_array = array (
            'id' => $id,
            'form_id' => $form_id,
            'name' => $this->plugin_name,
            'event' => $this->event,
            'option' => $option,
            'task' => 'save_conf');
        $hidden_array['params[onsubmit]'] = 'before_email';
        echo $helper->createHiddenArray( $hidden_array );
    }

	// this function must exist and may not be changed unless you need to customize something
    function save_conf( $option )
    {
        global $mainframe;

        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'
            .DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();
        $helper->save_conf($option);
    }

    function onsubmit( $option, $params , $row )
    {
        global $mainframe;

        /*require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_chronocontact'
            .DS.'helpers'.DS.'plugin.php');
        $helper = new ChronoContactHelperPlugin();*/

        //$user =& JFactory::getUser();

        $formname  = JRequest::getVar( 'chronoformname');
		$MyForm    =& CFChronoForm::getInstance($formname);
		//echo '<div>$MyForm->formparams(uploadpath): '.print_r($MyForm->formparams('uploadpath'), true).'</div>';

        // Common parameters
        $formname		    = JRequest::getVar('chronoformname', 'unknown');
        $chronouploads 		= $MyForm->formparams('uploadpath');
        $photo				= JRequest::getVar($params->get('photo'), '', 'post', 'string', JREQUEST_ALLOWRAW);
        $quality			= $params->get('quality');
        $filein 			= $chronouploads.$photo;

        $dir = '';
        if ( $params->get('big_directory') ) {
            $dir .= $params->get('big_directory');
        } else {
            $dir .= $chronouploads;
        }
        // add a final slash if needed
        if ( substr($dir, -1) != DS ) {
            $dir .= DS;
        }

        // treatment of the large image
        $fileout 			= $dir.$params->get('big_image_prefix').$photo.$params->get('big_image_suffix');
        $crop 				= $params->get('big_image_method');
        $imagethumbsize_w 	= $params->get('big_image_width');
        $imagethumbsize_h 	= $params->get('big_image_height');
        $red				= $params->get('big_image_r');
        $green				= $params->get('big_image_g');
        $blue				= $params->get('big_image_b');

        if ( $crop ) {
            $this->resizeThenCrop( $filein, $fileout, $imagethumbsize_w, $imagethumbsize_h, $red, $green, $blue, $quality );
        } else {
            $this->resize( $filein, $fileout, $imagethumbsize_w, $imagethumbsize_h, $red, $green, $blue, $quality );
        }

        // treatment of the medium image
        $dir = '';
        if ( $params->get('med_directory') ) {
            $dir .= $params->get('med_directory');
        } else {
            $dir .= $chronouploads;
        }
        // add a final slash if needed
        if ( substr($dir, -1) != DS ) {
            $dir .= DS;
        }

        $fileout 			= $dir.$params->get('med_image_prefix').$photo.$params->get('med_image_suffix');
        $crop 				= $params->get('med_image_method');
		$imagethumbsize_w 	= $params->get('med_image_width');
		$imagethumbsize_h 	= $params->get('med_image_height');
		$red				= $params->get('med_image_r');
		$green				= $params->get('med_image_g');
		$blue				= $params->get('med_image_b');
		$usemed				= $params->get('med_image_use');

		if ( $usemed ) {
			if ( $crop ) {
				$this->resizeThenCrop( $filein, $fileout, $imagethumbsize_w, $imagethumbsize_h, $red, $green, $blue, $quality );
			} else {
				$this->resize( $filein, $fileout, $imagethumbsize_w, $imagethumbsize_h, $red, $green, $blue, $quality );
			}
		}

		// treatment of the small image
        $dir = '';
        if ( $params->get('small_directory') ) {
            $dir .= $params->get('small_directory');
        } else {
            $dir .= $chronouploads;
        }
        // add a final slash if needed
        if ( substr($dir, -1) != DS ) {
            $dir .= DS;
        }
		$fileout 			= $dir.$params->get('small_image_prefix').$photo.$params->get('small_image_suffix');
		$crop 				= $params->get('small_image_method');
		$imagethumbsize_w 	= $params->get('small_image_width');
		$imagethumbsize_h 	= $params->get('small_image_height');
		$red				= $params->get('small_image_r');
		$green				= $params->get('small_image_g');
		$blue				= $params->get('small_image_b');
		$usesmall			= $params->get('small_image_use');

		if ( $usesmall ) {
			if ($crop) {
				$this->resizeThenCrop( $filein, $fileout, $imagethumbsize_w, $imagethumbsize_h, $red, $green, $blue, $quality );
			} else {
			    $this->resize( $filein, $fileout, $imagethumbsize_w, $imagethumbsize_h, $red, $green, $blue, $quality );
			}
		}

		if ( $params->get('delete_original') ) {
		    unlink($filein);
		}
	}

	function resizeThenCrop( $filein, $fileout, $imagethumbsize_w, $imagethumbsize_h, $red, $green, $blue, $quality )
	{
        // Cacul des nouvelles dimensions
        list($width, $height) = getimagesize($filein);
        //$new_width  = $width * $percent;
        //$new_height = $height * $percent;

        if ( preg_match("/.jpg/i", "$filein") ) {
            $format = 'image/jpeg';
        } elseif ( preg_match("/.gif/i", "$filein") ) {
            $format = 'image/gif';
        } else if( preg_match("/.png/i", "$filein") ) {
            $format = 'image/png';
        }

        switch($format) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filein);
                break;
            case 'image/gif';
                $image = imagecreatefromgif($filein);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filein);
                break;
        }

        $width  = $imagethumbsize_w ;
        $height = $imagethumbsize_h ;
        list($width_orig, $height_orig) = getimagesize($filein);

        if ( $width_orig < $height_orig ) {
            $height = ($imagethumbsize_w / $width_orig) * $height_orig;
        } else {
            $width  = ($imagethumbsize_h / $height_orig) * $width_orig;
        }

        if ( $width < $imagethumbsize_w ) {
            // If the image width is less than the thumbnail
            $width  = $imagethumbsize_w;
            $height = ($imagethumbsize_w / $width_orig) * $height_orig;
        }

        if ( $height < $imagethumbsize_h ) {
            // If the image height is less than the thumbnail

            $height = $imagethumbsize_h;
            $width  = ($imagethumbsize_h / $height_orig) * $width_orig;
        }

        $thumb   = imagecreatetruecolor($width , $height);
        $bgcolor = imagecolorallocate($thumb, $red, $green, $blue);
        ImageFilledRectangle($thumb, 0, 0, $width, $height, $bgcolor);
        imagealphablending($thumb, true);

        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
        $thumb2 = imagecreatetruecolor($imagethumbsize_w , $imagethumbsize_h);
        // true color for better quality
        $bgcolor = imagecolorallocate($thumb2, $red, $green, $blue);
        ImageFilledRectangle($thumb2, 0, 0, $imagethumbsize_w, $imagethumbsize_h, $bgcolor);
        imagealphablending($thumb2, true);

        $w1 = ($width  / 2) - ($imagethumbsize_w / 2);
        $h1 = ($height / 2) - ($imagethumbsize_h  / 2);

        imagecopyresampled($thumb2, $thumb, 0, 0, $w1, $h1,$imagethumbsize_w, $imagethumbsize_h, $imagethumbsize_w, $imagethumbsize_h);

        // create the file
        switch($format) {
            case 'image/jpeg':
                imagejpeg($thumb2, $fileout, $quality);
                break;

            case 'image/gif';
                imagegif($thumb2, $fileout);
                break;

            case 'image/png':
                imagepng($thumb2, $fileout);
                break;
        }
	}


    function resize( $filein, $fileout, $imagethumbsize_w, $imagethumbsize_h, $red, $green, $blue, $quality )
    {
        // Cacul des nouvelles dimensions
        list($width_orig, $height_orig) = getimagesize($filein);

        if ( preg_match("/.jpg/i", "$filein") ) {
            $format = 'image/jpeg';
        }
        if ( preg_match("/.gif/i", "$filein") ) {
            $format = 'image/gif';
        }
        if ( preg_match("/.png/i", "$filein") ) {
            $format = 'image/png';
        }

        switch ( $format ) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filein);
                break;
            case 'image/gif';
                $image = imagecreatefromgif($filein);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filein);
                break;
        }

        $ratio_orig = $width_orig/$height_orig;

        if ($imagethumbsize_w/$imagethumbsize_h > $ratio_orig) {
            $imagethumbsize_w = $imagethumbsize_h*$ratio_orig;
        } else {
            $imagethumbsize_h = $imagethumbsize_w/$ratio_orig;
        }

        // Redimensionnement
        $thumb3  = imagecreatetruecolor($imagethumbsize_w, $imagethumbsize_h);
        $bgcolor = imagecolorallocate($thumb3, $red, $green, $blue);
        ImageFilledRectangle($thumb3, 0 ,0 ,$imagethumbsize_w,
            $imagethumbsize_h, $bgcolor);
        imagealphablending($thumb3, true);

        imagecopyresampled($thumb3, $image, 0, 0, 0, 0, $imagethumbsize_w,
            $imagethumbsize_h, $width_orig, $height_orig);

        switch ( $format ) {
            case 'image/jpeg':
                imagejpeg($thumb3, $fileout, $quality); // on cree le fichier
                break;
            case 'image/gif';
                imagegif($thumb3, $fileout); // on cree le fichier
                break;
            case 'image/png':
                imagepng($thumb3, $fileout); // on cree le fichier
                break;
        }
    }
}
?>