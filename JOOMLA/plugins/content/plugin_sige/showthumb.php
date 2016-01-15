<?php

/*

// "Simple Image Gallery" Extended Plugin Joomla 1.5 - Version 1.5-15

// License: http://www.gnu.org/copyleft/gpl.html

// Author: Viktor Vogel

// Projectsite: http://joomla-extensions.kubik-rubik.de/sige-simple-image-gallery-extended

// Based on: Simple Image Gallery - www.joomlaworks.gr

//

// @license GNU/GPL

// This program is free software: you can redistribute it and/or modify

// it under the terms of the GNU General Public License as published by

// the Free Software Foundation, either version 3 of the License, or

// (at your option) any later version.

//

// This program is distributed in the hope that it will be useful,

// but WITHOUT ANY WARRANTY; without even the implied warranty of

// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

// GNU General Public License for more details.

//

// You should have received a copy of the GNU General Public License

// along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/



if ($_GET['img'] == "") 

{

	exit("Keine Parameter!");

}



$_GET['img'] = str_replace( '..', '', urldecode( $_GET['img'] ) );

$_image_ = '../../..'.$_GET['img'];



$_width_ = htmlspecialchars(intval($_GET['width']));

$_height_ = htmlspecialchars(intval($_GET['height']));

$_quality_ = htmlspecialchars(intval($_GET['quality']));

$ratio = htmlspecialchars(intval($_GET['ratio']));

$crop = htmlspecialchars(intval($_GET['crop']));

$crop_factor = htmlspecialchars(intval($_GET['crop_factor']));

$thumbdetail = htmlspecialchars(intval($_GET['thumbdetail']));



$imagedata = getimagesize($_image_);


if (!$imagedata[0]) 

{
	exit();

}



$new_w = $_width_;



if ($ratio) 

{

	$new_h = (int)($imagedata[1]*($new_w/$imagedata[0]));

	if($_height_ AND ($new_h > $_height_)) 

	{

		$new_h = $_height_;

		$new_w = (int)($imagedata[0]*($new_h/$imagedata[1]));

	} 

} 

else 

{

	$new_h = $_height_;

}




// CROP - 1.5.12

$width_ori = $imagedata[0];

$height_ori = $imagedata[1];



// Crop - Ausschnitt des Bildes anzeigen - 1.5.12

if ($crop AND ($crop_factor > 0 AND $crop_factor < 100)) 

{

	// Größere Seite auswählen - für quadratische Thumbnails

	if ($width_ori > $height_ori) 

	{

		$biggest_side = $width_ori;

	} 

	else 

	{

		$biggest_side = $height_ori; 

	}

	// Cropfaktor setzen

	$crop_percent = (1 - ($crop_factor / 100));

	

	if (!$ratio AND ($_width_ == $_height_)) 

	{ // Keine Seitenverhältnisse und quadratisch

		$crop_width = $biggest_side * $crop_percent; 

		$crop_height = $biggest_side * $crop_percent;

	}

	elseif (!$ratio AND ($_width_ != $_height_)) 

	{ // Keine Seitenverhältnisse und rechteckig

		if (($width_ori / $_width_) < ($height_ori / $_height_)) 

		{

			$crop_width   = $width_ori * $crop_percent; 

			$crop_height  = ($_height_ * ($width_ori / $_width_)) * $crop_percent;

		} 

		else 

		{

			$crop_width   = ($_width_ * ($height_ori / $_height_)) * $crop_percent; 

			$crop_height  =  $height_ori * $crop_percent;

		}							

	} 

	else 

	{ // Seitenverhältnisse beibehalten

		$crop_width = $width_ori * $crop_percent; 

		$crop_height = $height_ori * $crop_percent;

	}

	$x_coordinate = ($width_ori - $crop_width) / 2;

	$y_coordinate = ($height_ori - $crop_height) / 2;

}



if (strtolower(substr($_GET['img'],-3)) == "jpg") 

{

	header("Content-type: image/jpg");

	$src_img = imagecreatefromjpeg($_image_);

	$dst_img = imagecreatetruecolor($new_w, $new_h);

	

	if ($crop AND ($crop_factor > 0 AND $crop_factor < 100)) 

	{

		imagecopyresampled($dst_img, $src_img, 0, 0, $x_coordinate, $y_coordinate, $new_w, $new_h, $crop_width, $crop_height);

	} 

	else 

	{

		if ($thumbdetail == 1) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_w, $new_h, $new_w, $new_h);

		} 

		elseif ($thumbdetail == 2) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, $width_ori - $new_w, 0, $new_w, $new_h, $new_w, $new_h);

		} 

		elseif ($thumbdetail == 3) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);

		} 

		elseif ($thumbdetail == 4) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, $width_ori - $new_w, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);	

		} 

		else 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);

		}

	}

	$img = imagejpeg($dst_img,'', $_quality_);

	imagedestroy($src_img);

	imagedestroy($dst_img);

	imagedestroy($img);

}



if (substr($_GET['img'],-3) == "gif") 

{

	header("Content-type: image/gif");

	$src_img = imagecreateFromGif($_image_);

	$dst_img = imagecreatetruecolor($new_w, $new_h);		

	imagepalettecopy($dst_img,$src_img);

	

	if ($crop AND ($crop_factor > 0 AND $crop_factor < 100)) 

	{

		imagecopyresampled($dst_img, $src_img, 0, 0, $x_coordinate, $y_coordinate, $new_w, $new_h, $crop_width, $crop_height);

	} 

	else 

	{

		if ($thumbdetail == 1) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_w, $new_h, $new_w, $new_h);

		} 

		elseif ($thumbdetail == 2) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, $width_ori - $new_w, 0, $new_w, $new_h, $new_w, $new_h);

		} 

		elseif ($thumbdetail == 3) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);

		} 

		elseif ($thumbdetail == 4) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, $width_ori - $new_w, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);	

		} 

		else 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);

		}

	}

	$img = imagegif($dst_img, '', $_quality_);

	imagedestroy($src_img);

	imagedestroy($dst_img);

	imagedestroy($img);

}



if (substr($_GET['img'],-3) == "png") 

{

	header("Content-type: image/png");

	$src_img = imagecreatefrompng($_image_);

	$dst_img = imagecreatetruecolor($new_w, $new_h); 

	imagepalettecopy($dst_img,$src_img);

	

	if ($crop AND ($crop_factor > 0 AND $crop_factor < 100)) 

	{

		imagecopyresampled($dst_img, $src_img, 0, 0, $x_coordinate, $y_coordinate, $new_w, $new_h, $crop_width, $crop_height);

	} 

	else 

	{

		if ($thumbdetail == 1) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_w, $new_h, $new_w, $new_h);

		} 

		elseif ($thumbdetail == 2) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, $width_ori - $new_w, 0, $new_w, $new_h, $new_w, $new_h);

		} 

		elseif ($thumbdetail == 3) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);

		} 

		elseif ($thumbdetail == 4) 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, $width_ori - $new_w, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);	

		} 

		else 

		{

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);

		}

	}

	$img = imagepng($dst_img, '', 6);

	imagedestroy($src_img);

	imagedestroy($dst_img);

	imagedestroy($img);

}

?>

