<?php
/*
// "Simple Image Gallery" Extended Plugin Joomla 1.5 - Version 1.5-15
// License: http://www.gnu.org/copyleft/gpl.html
// Author: Viktor Vogel
// Projectsite: http://joomla-extensions.kubik-rubik.de/sige-simple-image-gallery-extended
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

// Bei Aufruf ohne Parameter beenden
if ($_GET['img'] == "") 
{
	exit("Keine Parameter!");
}

$_GET['img'] = str_replace( '..', '', urldecode( $_GET['img'] ) );
$_image_ = rawurldecode('../../..'.$_GET['img']);
$datei = basename($_image_);

// Wenn Bilddatei, dann Download starten, sonst beenden
if ( (substr(strtolower($datei),-3) == 'jpg') OR (substr(strtolower($datei),-3) == 'gif') OR (substr(strtolower($datei),-3) == 'png') ) 
{
	$size = filesize($_image_);
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=".$datei);
	header("Content-Length:".$size);
	readfile($_image_); 
} 
else 
{ // Warnhinweis anzeigen
	exit("$datei ist kein Bildformat! Akzeptiert werden nur: jpg, gif und png.");
}
?>
