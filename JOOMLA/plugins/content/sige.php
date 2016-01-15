<?php
/*
// SIGE - "Simple Image Gallery Extended" Plugin Joomla 1.5 - Version 1.5-15
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');

class plgContentSige extends JPlugin
{
	function plgContentSige(&$subject)
	{
		parent::__construct($subject);
		$this->_plugin = JPluginHelper::getPlugin('content', 'sige');
		$this->_params = new JParameter($this->_plugin->params);
		if (isset($_SESSION["sigcount"]))
		{
			unset($_SESSION["sigcount"]);
		}
		if (isset($_SESSION["sigcountarticles"]))
		{
			unset($_SESSION["sigcountarticles"]);
		}
		// Sprache aus Administrator einlesen
		$this->loadLanguage('plg_content_sige', JPATH_ADMINISTRATOR);
	}

	function onPrepareContent(&$row, &$params, $limitstart)
	{
		global $mainframe;

		// Wenn Pluginsyntax nicht gefunden, nichts machen
		if (!preg_match("#{gallery}(.*?){/gallery}#s", $row->text))
		{
			return;
		}

		$plugin =& JPluginHelper::getPlugin('content', 'sige');
		$pluginParams = new JParameter($plugin->params);

		$mosConfig_absolute_path = JPATH_SITE;
		$mosConfig_live_site = JURI :: base();

		if (substr($mosConfig_live_site, -1) == "/")
		{
			$mosConfig_live_site = substr($mosConfig_live_site, 0, -1);
		}

		// GD Test - Bugfix "undefined variable" - removed eregi_replace (DEPRECATED) - 1.5-11-2
		if (function_exists("gd_info"))
		{
			$gdinfo = gd_info();
			$gdsupport = array();
			$version = intval(preg_replace('/[[:alpha:][:space:]()]+/', '', $gdinfo['GD Version']));
			if ($version!=2) $gdsupport[] = '<div class="message">GD Bibliothek nicht vorhanden</div>';
			// PHP Versionsabfrage, um Fehler zu verhindern 1.5-13
			if (substr(phpversion(), 0, 3) < 5.3)
			{
				if (!$gdinfo['JPG Support']) $gdsupport[] = '<div class="message">GD JPG Bibliothek nicht vorhanden</div>';
			}
			else
			{
				if (!$gdinfo['JPEG Support']) $gdsupport[] = '<div class="message">GD JPG Bibliothek nicht vorhanden</div>';
			}
			if (!$gdinfo['GIF Create Support']) $gdsupport[] = '<div class="message">GD GIF Bibliothek nicht vorhanden</div>';
			if (!$gdinfo['PNG Support']) $gdsupport[] = '<div class="message">GD PNG Bibliothek nicht vorhanden</div>';
			if (count($gdsupport))
			{
				foreach ($gdsupport as $k=>$v) {echo $v;}
			}
		}

		// Joomla Versionsabfrage
		$version = new JVersion();
		if ($version->PRODUCT == "Joomla!" AND $version->RELEASE != "1.5")
		{
			echo '<div class="message">Joomla 1.5 wird benötigt!</div>';
		}

		if (!isset($_SESSION["sigcountarticles"]))
		{
			$_SESSION["sigcountarticles"] = -1;
		}

		// Suche nach Pluginsyntax
		if (preg_match_all("#{gallery}(.*?){/gallery}#s", $row->text, $matches, PREG_PATTERN_ORDER) > 0)
		{
			$_SESSION["sigcountarticles"]++;
			if (!isset($_SESSION["sigcount"]))
			{
				$_SESSION["sigcount"] = -1;
			}

			$sige_css = "";
			foreach ($matches[0] as $match)
			{
				$_SESSION["sigcount"]++;
				$sige_code = preg_replace("/{.+?}/", "", $match);
				// Umwandlung des Strings in einen Array
				$sige_array = explode(",", $sige_code);
				// Bildverzeichnis steht an erster Stelle
				$_images_dir_ = $sige_array[0];

				unset($sige_parameter);
				$sige_parameter = array();

				// Existieren Parameter? Ggf. Leerstellen bei Eingabe der Parameter werden entfernt
				if (count($sige_array) >= 2)
				{
					for ($i=1; $i < count($sige_array); $i++)
					{
						$parameter_temp = explode("=",$sige_array[$i]);
						if (count($parameter_temp) >= 2)
						{
							$sige_parameter[strtolower(trim($parameter_temp[0]))] = trim($parameter_temp[1]);
						}
					}
				}

				// Parameter einlesen
				$root = (array_key_exists("root",$sige_parameter) AND $sige_parameter['root']!="")?($sige_parameter['root']):($pluginParams->get('root', 1));

				// Stammordner setzen
				if (!$root)
				{
					$rootfolder = '/images/stories/';
				}
				else
				{
					$rootfolder = '/';
				}

				unset($images);
				$noimage = 0;

				// Bilder einlesen
				if ($dh = @opendir($mosConfig_absolute_path.$rootfolder.$_images_dir_))
				{
					while (($f = readdir($dh)) !== false)
					{
						if (substr(strtolower($f),-3) == 'jpg' OR substr(strtolower($f),-3) == 'gif' OR substr(strtolower($f),-3) == 'png')
						{
							$images[] = array('filename' => $f);
							$noimage++;
						}
					}
					closedir($dh);
				}

				// Bilderausgabe, falls im angegeben Ordner vorhanden, sonst Anzeige einer Fehlermeldung
				if ($noimage)
				{
					// Parameter durch Syntax / Einstellungen setzen
					$_width_ 	= (array_key_exists("width",$sige_parameter) AND $sige_parameter['width']!="")?($sige_parameter['width']):($pluginParams->get('th_width', 200));
					$_height_ 	= (array_key_exists("height",$sige_parameter) AND $sige_parameter['height']!="")?($sige_parameter['height']):($pluginParams->get('th_height', 200));
					$ratio		= (array_key_exists("ratio",$sige_parameter) AND $sige_parameter['ratio']!="")?($sige_parameter['ratio']):($pluginParams->get('ratio', 1));
					$gap_v		= (array_key_exists("gap_v",$sige_parameter) AND $sige_parameter['gap_v']!="")?($sige_parameter['gap_v']):($pluginParams->get('gap_v', 30));
					$gap_h		= (array_key_exists("gap_h",$sige_parameter) AND $sige_parameter['gap_h']!="")?($sige_parameter['gap_h']):($pluginParams->get('gap_h', 20));
					$_quality_ 	= (array_key_exists("quality",$sige_parameter) AND $sige_parameter['quality']!="")?($sige_parameter['quality']):($pluginParams->get('th_quality', 80));
					$_quality_png	= (array_key_exists("quality_png",$sige_parameter) AND $sige_parameter['quality_png']!="")?($sige_parameter['quality_png']):($pluginParams->get('th_quality_png', 6));
					$displaynavtip 	= (array_key_exists("displaynavtip",$sige_parameter) AND $sige_parameter['displaynavtip']!="")?($sige_parameter['displaynavtip']):($pluginParams->get('displaynavtip', 1));
					$navtip 		= $pluginParams->get('navtip');
					$displaymessage = (array_key_exists("displayarticle",$sige_parameter) AND $sige_parameter['displayarticle']!="")?($sige_parameter['displayarticle']):($pluginParams->get('displaymessage', 1));
					$message 	= $pluginParams->get('message');
					$thumbs		= (array_key_exists("thumbs",$sige_parameter) AND $sige_parameter['thumbs']!="")?($sige_parameter['thumbs']):($pluginParams->get('thumbs', 1));
					$thumbs_new	= $pluginParams->get('thumbs_new', 1);
					$view		= $pluginParams->get('view', 0);
					$limit		= (array_key_exists("limit",$sige_parameter) AND $sige_parameter['limit']!="")?($sige_parameter['limit']):($pluginParams->get('limit', 0));
					$limit_quantity	= (array_key_exists("limit_quantity",$sige_parameter) AND $sige_parameter['limit_quantity']!="")?($sige_parameter['limit_quantity']):($pluginParams->get('limit_quantity', 10));
					$noslim	= (array_key_exists("noslim",$sige_parameter) AND $sige_parameter['noslim']!="")?($sige_parameter['noslim']):($pluginParams->get('noslim', 0));
					$caption = (array_key_exists("caption",$sige_parameter) AND $sige_parameter['caption']!="")?($sige_parameter['caption']):($pluginParams->get('caption', 0));
					// Version 1.5-10
					// IPTC Daten der Bilder einlesen
					$iptc = (array_key_exists("iptc",$sige_parameter) AND $sige_parameter['iptc']!="")?($sige_parameter['iptc']):($pluginParams->get('iptc', 0));
					// IPTC UTF8-kodiert
					$iptcutf8 = (array_key_exists("iptcutf8",$sige_parameter) AND $sige_parameter['iptcutf8']!="")?($sige_parameter['iptcutf8']):($pluginParams->get('iptcutf8', 0));
					// Druckbutton einblenden
					$print = (array_key_exists("print",$sige_parameter) AND $sige_parameter['print']!="")?($sige_parameter['print']):($pluginParams->get('print', 0));
					// Version 1.5-11
					// Einzelbild ausrichten
					$salign	= (array_key_exists("salign",$sige_parameter) AND $sige_parameter['salign']!="")?($sige_parameter['salign']):($pluginParams->get('salign', 0));
					// Einzelbilder in Galerie verbinden
					$connect	= (array_key_exists("connect",$sige_parameter) AND $sige_parameter['connect']!="")?($sige_parameter['connect']):($pluginParams->get('connect', 0));
					// Downloadbutton einblenden
					$download = (array_key_exists("download",$sige_parameter) AND $sige_parameter['download']!="")?($sige_parameter['download']):($pluginParams->get('download', 0));
					// Bilder als Liste anzeigen
					$list	= (array_key_exists("list",$sige_parameter) AND $sige_parameter['list']!="")?($sige_parameter['list']):($pluginParams->get('list', 0));
					// Crop - Ausschnitt
					$crop	= (array_key_exists("crop",$sige_parameter) AND $sige_parameter['crop']!="")?($sige_parameter['crop']):($pluginParams->get('crop', 0));
					$crop_factor	= (array_key_exists("crop_factor",$sige_parameter) AND $sige_parameter['crop_factor']!="")?($sige_parameter['crop_factor']):($pluginParams->get('crop_factor', 0));
					$random = (array_key_exists("random",$sige_parameter) AND $sige_parameter['random']!="")?($sige_parameter['random']):($pluginParams->get('random', 2));
					$single	= (array_key_exists("single",$sige_parameter) AND $sige_parameter['single']!="")?($sige_parameter['single']):($pluginParams->get('single', 0));
					// Version 1.5-13
					// Thumbnaildetail
					$thumbdetail	= (array_key_exists("thumbdetail",$sige_parameter) AND $sige_parameter['thumbdetail']!="")?($sige_parameter['thumbdetail']):($pluginParams->get('thumbdetail', 0));
					// Wasserzeichen
					$watermark	= (array_key_exists("watermark",$sige_parameter) AND $sige_parameter['watermark']!="")?($sige_parameter['watermark']):($pluginParams->get('watermark', 0));
					// Wasserzeichenausrichtung
					$watermarkposition	= (array_key_exists("watermarkposition",$sige_parameter) AND $sige_parameter['watermarkposition']!="")?($sige_parameter['watermarkposition']):($pluginParams->get('watermarkposition', 0));
					// Wassereichenbilder überschreiben
					$watermark_new	= $pluginParams->get('watermark_new', 1);
					// Verschlüsselungsmethode
					$encrypt	= (array_key_exists("encrypt",$sige_parameter) AND $sige_parameter['encrypt']!="")?($sige_parameter['encrypt']):($pluginParams->get('encrypt', 1));
					$image_info	= (array_key_exists("image_info",$sige_parameter) AND $sige_parameter['image_info']!="")?($sige_parameter['image_info']):($pluginParams->get('image_info', 1));
					// Version 1.5-14
					$image_link	= (array_key_exists("image_link",$sige_parameter) AND $sige_parameter['image_link']!="")?($sige_parameter['image_link']):($pluginParams->get('image_link', 0));
					$image_link_new	= (array_key_exists("image_link_new",$sige_parameter) AND $sige_parameter['image_link_new']!="")?($sige_parameter['image_link_new']):($pluginParams->get('image_link', 1));
					$single_gallery	= (array_key_exists("single_gallery",$sige_parameter) AND $sige_parameter['single_gallery']!="")?($sige_parameter['single_gallery']):'';
					$column_quantity = (array_key_exists("column_quantity",$sige_parameter) AND $sige_parameter['column_quantity']!="")?($sige_parameter['column_quantity']):($pluginParams->get('column_quantity', 0));
					$css_image = (array_key_exists("css_image",$sige_parameter) AND $sige_parameter['css_image']!="")?($sige_parameter['css_image']):($pluginParams->get('css_image', 1));
					$css_image_half = (array_key_exists("css_image_half",$sige_parameter) AND $sige_parameter['css_image_half']!="")?($sige_parameter['css_image_half']):($pluginParams->get('css_image_half', 1));
					$copyright = (array_key_exists("copyright",$sige_parameter) AND $sige_parameter['copyright']!="")?($sige_parameter['copyright']):($pluginParams->get('copyright', 1));
					// Version  1.5-14-1
					$mootoolsupgrade	= $pluginParams->get('mootoolsupgrade', 1);
					// Galerie aus Wort - ohne Thumbnail
					if (array_key_exists("word", $sige_parameter) AND $sige_parameter['word'] != "")
					{
						$word_gallery = $sige_parameter['word'];
					}
					else
					{
						$word_gallery = false;
					}

					// Wasserzeichenbild über Parameter setzen
					if (array_key_exists("watermarkimage", $sige_parameter) AND $sige_parameter['watermarkimage'] != "")
					{
						$watermarkimg = $sige_parameter['watermarkimage'];
					}
					else
					{
						$watermarkimg = false;
					}

					// $sigcount manuell setzen - Aufruf mit count=ZAHL - 1.5-10
					if (array_key_exists("count",$sige_parameter) AND $sige_parameter['count'] != "")
					{
						$_SESSION["sigcount"] = $sige_parameter['count'];
					}

					$itemtitle = preg_replace("/\"/", "'", $row->title);

					// CSS Anweisung für jede einzelne Galerie -- Einzelbilder ausrichten - 1.5-11
					if ($salign)
					{
						if ($salign == 'left')
						{
							$sige_css .= ".sige_cont_".$_SESSION["sigcount"]." {width:".($_width_+$gap_h)."px;height:".($_height_+$gap_v)."px;float:left;}\n";
						}
						elseif ($salign == 'right')
						{
							$sige_css .= ".sige_cont_".$_SESSION["sigcount"]." {width:".($_width_+$gap_h)."px;height:".($_height_+$gap_v)."px;float:right;}\n";
						}
					}
					else
					{
						$sige_css .= ".sige_cont_".$_SESSION["sigcount"]." {width:".($_width_+$gap_h)."px;height:".($_height_+$gap_v)."px;float:left;}\n";
					}

					// Sortierung der Bilder - zufällig, auf- oder absteigend - 1.5-11-2
					if ($random == 1)
					{
						shuffle($images);
					}
					elseif ($random == 2)
					{
						sort($images);
					}
					elseif ($random == 3)
					{
						rsort($images);
					}
					elseif ($random == 4 OR $random == 5)
					{ // Sortierung nach Änderungsdatum - 1.5-13
						for ($a = 0; $a < count($images); $a++)
						{
							$images[$a]['timestamp'] = filemtime($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);
						}
						if ($random == 4)
						{
							usort($images, array($this,'timeasc'));
						}
						elseif ($random == 5)
						{
							usort($images, array($this,'timedesc'));
						}
					}

					// Einzelbild gewählt -> Prüfen, ob es vorhanden ist. Wenn ja, Array und Zählvariable neu setzen! - 1.5-10
					// Einzebild mit Galerie - 1.5-14
					if ($single)
					{
						$single_yes = false;
						$count = count($images);

						if ($images[0]['filename'] == $single)
						{
							if ($single_gallery)
							{
								$noimage_rest = $noimage;
								$limit_quantity = 1;
							}
							$noimage = 1;
							$single_yes = true;
						}
						else
						{
							for ($a = 1; $a < $noimage; $a++)
							{
								if ($images[$a]['filename'] == $single)
								{
									if ($single_gallery)
									{
										$noimage_rest = $noimage;
										$limit_quantity = 1;
									}
									$noimage = 1;
									$images[$count] = $images[0];
									$images[0] = array('filename' => $single);
									unset($images[$a]);
									$images[$a] = $images[$count];
									unset($images[$count]);
									$single_yes = true;
								}
							}
						}
					}

					// Start der Ausgabe
					$html = '<!-- Simple Image Gallery Extended - Plugin J1.5-15 - http://joomla-extensions.kubik-rubik.de/sige-simple-image-gallery-extended -->';

					// CSS einbinden abhängig, ob Einzelbild oder Wortverlinkung gewählt wurde - 1.5-11
					if ($single AND $single_yes AND !$word_gallery)
					{
						$html .= '<span class="sige_single">';
					}
					elseif (!$list AND !$word_gallery)
					{
						$html .= '<span class="sige">';
					}

					// Liste gesetzt - 1.5.11 - nicht bei Wortverlinkung
					if ($list AND !$word_gallery)
					{
						$html .= '<ul>';
					}

					// Wasserzeichen - 1.5-13
					if ($watermark)
					{
						for ($a = 0; $a < $noimage; $a++)
						{
							if ($images[$a]['filename'] != '')
							{
								// Thumbnailordner wird erstellt, falls noch nicht vorhanden
								if (!is_dir($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/wm'))
								{
									mkdir($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/wm', 0755);
								}
								// Bildname, Endung, Verschlüsselungsmethode - 1.5-13
								$imagename = substr($images[$a]['filename'], 0, -4);
								$type = substr(strtolower($images[$a]['filename']),-3);
								$image_hash = $this->encrypt($encrypt,$imagename).'.'.$type;

								$filenamewm = $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/wm/'.$image_hash;
								if (!file_exists($filenamewm) OR $watermark_new != 0)
								{
									if ($watermarkimg)
									{
										$watermarkimage = imagecreatefrompng($mosConfig_absolute_path.'/plugins/content/plugin_sige/'.$watermarkimg);
										list($width_wm, $height_wm) = getimagesize($mosConfig_absolute_path.'/plugins/content/plugin_sige/'.$watermarkimg);
									}
									else
									{
										$watermarkimage = imagecreatefrompng($mosConfig_absolute_path.'/plugins/content/plugin_sige/watermark.png');
										list($width_wm, $height_wm) = getimagesize($mosConfig_absolute_path.'/plugins/content/plugin_sige/watermark.png');
									}
									$imagedata = getimagesize($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);

									// Wasserzeichen je nach Bildtyp erstellen
									if (substr(strtolower($images[$a]['filename']),-3) == 'gif')
									{
										$origimage = imagecreatefromgif($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);
										// Truecolor erzeugen
										$t_image = imagecreatetruecolor($imagedata[0], $imagedata[1]);
										imagecopy($t_image, $origimage, 0, 0, 0, 0, $imagedata[0], $imagedata[1]);
										$origimage = $t_image;

										if ($watermarkposition == 1)
										{
											imagecopy($origimage, $watermarkimage, 0, 0, 0, 0, $width_wm, $height_wm);
										}
										elseif ($watermarkposition == 2)
										{
											imagecopy($origimage, $watermarkimage, $imagedata[0] - $width_wm, 0, 0, 0, $width_wm, $height_wm);
										}
										elseif ($watermarkposition == 3)
										{
											imagecopy($origimage, $watermarkimage, 0, $imagedata[1] - $height_wm, 0, 0, $width_wm, $height_wm);
										}
										elseif ($watermarkposition == 4)
										{
											imagecopy($origimage, $watermarkimage, $imagedata[0] - $width_wm, $imagedata[1] - $height_wm, 0, 0, $width_wm, $height_wm);
										}
										else
										{
											imagecopy($origimage, $watermarkimage, ($imagedata[0] - $width_wm)/2, ($imagedata[1] - $height_wm)/2, 0, 0, $width_wm, $height_wm);
										}
										imagegif($origimage, $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/wm/'.$image_hash);
										imagedestroy($origimage);
										imagedestroy($watermarkimage);
									}
									elseif (substr(strtolower($images[$a]['filename']),-3) == 'jpg')
									{
										$origimage = imagecreatefromjpeg($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);
										if ($watermarkposition == 1)
										{
											imagecopy($origimage, $watermarkimage, 0, 0, 0, 0, $width_wm, $height_wm);
										}
										elseif ($watermarkposition == 2)
										{
											imagecopy($origimage, $watermarkimage, $imagedata[0] - $width_wm, 0, 0, 0, $width_wm, $height_wm);
										}
										elseif ($watermarkposition == 3)
										{
											imagecopy($origimage, $watermarkimage, 0, $imagedata[1] - $height_wm, 0, 0, $width_wm, $height_wm);
										}
										elseif ($watermarkposition == 4)
										{
											imagecopy($origimage, $watermarkimage, $imagedata[0] - $width_wm, $imagedata[1] - $height_wm, 0, 0, $width_wm, $height_wm);
										}
										else
										{
											imagecopy($origimage, $watermarkimage, ($imagedata[0] - $width_wm)/2, ($imagedata[1] - $height_wm)/2, 0, 0, $width_wm, $height_wm);
										}
										imagejpeg($origimage, $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/wm/'.$image_hash , $_quality_);
										imagedestroy($origimage);
										imagedestroy($watermarkimage);
									}
									elseif (substr(strtolower($images[$a]['filename']),-3) == 'png')
									{
										$origimage = imagecreatefrompng($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);
										if ($watermarkposition == 1)
										{
											imagecopy($origimage, $watermarkimage, 0, 0, 0, 0, $width_wm, $height_wm);
										}
										elseif ($watermarkposition == 2)
										{
											imagecopy($origimage, $watermarkimage, $imagedata[0] - $width_wm, 0, 0, 0, $width_wm, $height_wm);
										}
										elseif ($watermarkposition == 3)
										{
											imagecopy($origimage, $watermarkimage, 0, $imagedata[1] - $height_wm, 0, 0, $width_wm, $height_wm);
										}
										elseif ($watermarkposition == 4)
										{
											imagecopy($origimage, $watermarkimage, $imagedata[0] - $width_wm, $imagedata[1] - $height_wm, 0, 0, $width_wm, $height_wm);
										}
										else
										{
											imagecopy($origimage, $watermarkimage, ($imagedata[0] - $width_wm)/2, ($imagedata[1] - $height_wm)/2, 0, 0, $width_wm, $height_wm);
										}
										imagepng($origimage, $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/wm/'.$image_hash , $_quality_png);
										imagedestroy($origimage);
										imagedestroy($watermarkimage);
									}
								}
							}
						}
					}

					// Vorschaubilderspeicherung gewählt - keine Liste und Wortverknüpfung
					if ($thumbs AND !$list AND !$word_gallery)
					{
						// Thumbnailordner wird erstellt, falls noch nicht vorhanden
						if (!is_dir($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/thumbs') AND $thumbs == 1)
						{
							mkdir($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/thumbs', 0755);
						}
						// Bildschleife - alle Bilder abarbeiten
						for ($a = 0; $a < $noimage; $a++)
						{
							if ($images[$a]['filename'] != '')
							{
								// Dateityp im Bildnamen entfernen
								$imagename = substr($images[$a]['filename'], 0, -4);
								$type = substr(strtolower($images[$a]['filename']),-3);
								$image_hash = $this->encrypt($encrypt,$imagename).'.'.$type;

								if ($watermark)
								{
									$filenamethumb = $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/thumbs/'.$image_hash;
								}
								else
								{
									$filenamethumb = $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/thumbs/'.$images[$a]['filename'];
								}

								// Größe der Thumbnails festlegen
								$new_w = $_width_;
								// Berechnung der Proportionen des Bildes - oder gleich wie die Eingabe
								if ($ratio)
								{
									$imagedata = getimagesize($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);

									$new_h = (int)($imagedata[1]*($new_w/$imagedata[0]));
									if (($_height_) AND ($new_h > $_height_))
									{
										$new_h = $_height_;
										$new_w = (int)($imagedata[0]*($new_h/$imagedata[1]));
									}
								}
								else
								{
									// Proportionen nicht beibehalten, Größe überschreiben
									$new_h = $_height_;
								}

								// Abfrage, ob ein neues Thumbnail generiert werden soll
								if (!file_exists($filenamethumb) OR $thumbs_new != 0)
								{
									if ($watermark)
									{
										list($width_ori, $height_ori) = getimagesize($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/wm/'.$image_hash);
									}
									else
									{
										list($width_ori, $height_ori) = getimagesize($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);
									}

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
										{
											// Keine Seitenverhältnisse und quadratisch
											$crop_width   = $biggest_side * $crop_percent;
											$crop_height  = $biggest_side * $crop_percent;
										}
										elseif (!$ratio AND ($_width_ != $_height_))
										{
											// Keine Seitenverhältnisse und rechteckig
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
										{
											// Seitenverhältnisse beibehalten
											$crop_width   = $width_ori * $crop_percent;
											$crop_height  = $height_ori * $crop_percent;
										}
										$x_coordinate = ($width_ori - $crop_width)/2;
										$y_coordinate = ($height_ori - $crop_height)/2;
									}

									// Thumbnails je nach Bildtyp erstellen
									if (substr(strtolower($filenamethumb),-3) == 'gif')
									{
										if ($watermark)
										{
											$origimage = imagecreatefromgif($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/wm/'.$image_hash);
										}
										else
										{
											$origimage = imagecreatefromgif($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);
										}
										$thumbimage = imagecreatetruecolor($new_w, $new_h);
										if ($crop AND ($crop_factor > 0 AND $crop_factor < 100))
										{
											imagecopyresampled($thumbimage, $origimage, 0, 0, $x_coordinate, $y_coordinate, $new_w, $new_h, $crop_width, $crop_height);
										}
										else
										{
											if ($thumbdetail == 1)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $new_w, $new_h);
											}
											elseif ($thumbdetail == 2)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, 0, $new_w, $new_h, $new_w, $new_h);
											}
											elseif ($thumbdetail == 3)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, 0, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
											}
											elseif ($thumbdetail == 4)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
											}
											else
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);
											}
										}
										if ($watermark)
										{
											imagegif($thumbimage, $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/thumbs/'.$image_hash);
										}
										else
										{
											imagegif($thumbimage, $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/thumbs/'.$images[$a]['filename']);
										}
										imagedestroy($origimage);
										imagedestroy($thumbimage);
									}
									elseif (substr(strtolower($filenamethumb),-3) == 'jpg')
									{
										if ($watermark)
										{
											$origimage = imagecreatefromjpeg($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/wm/'.$image_hash);
										}
										else
										{
											$origimage = imagecreatefromjpeg($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);
										}
										$thumbimage = imagecreatetruecolor($new_w, $new_h);
										if ($crop AND ($crop_factor > 0 AND $crop_factor < 100))
										{
											imagecopyresampled($thumbimage, $origimage, 0, 0, $x_coordinate, $y_coordinate, $new_w, $new_h, $crop_width, $crop_height);
										}
										else
										{
											if ($thumbdetail == 1)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $new_w, $new_h);
											}
											elseif ($thumbdetail == 2)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, 0, $new_w, $new_h, $new_w, $new_h);
											}
											elseif ($thumbdetail == 3)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, 0, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
											}
											elseif ($thumbdetail == 4)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
											}
											else
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);
											}
										}
										if ($watermark)
										{
											imagejpeg($thumbimage, $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/thumbs/'.$image_hash , $_quality_);
										}
										else
										{
											imagejpeg($thumbimage, $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/thumbs/'.$images[$a]['filename'] , $_quality_);
										}
										imagedestroy($origimage);
										imagedestroy($thumbimage);
									}
									elseif (substr(strtolower($filenamethumb),-3) == 'png')
									{

										if ($watermark)
										{
											$origimage = imagecreatefrompng($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/wm/'.$image_hash);
										}
										else
										{
											$origimage = imagecreatefrompng($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);
										}
										$thumbimage = imagecreatetruecolor($new_w, $new_h);
										if ($crop AND ($crop_factor > 0 AND $crop_factor < 100))
										{
											imagecopyresampled($thumbimage, $origimage, 0, 0, $x_coordinate, $y_coordinate, $new_w, $new_h, $crop_width, $crop_height);
										}
										else
										{
											if ($thumbdetail == 1)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $new_w, $new_h);
											}
											elseif ($thumbdetail == 2)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, 0, $new_w, $new_h, $new_w, $new_h);
											}
											elseif ($thumbdetail == 3)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, 0, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
											}
											elseif ($thumbdetail == 4)
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
											}
											else
											{
												imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);
											}
										}
										if ($watermark)
										{
											imagepng($thumbimage, $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/thumbs/'.$image_hash, $_quality_png);
										}
										else
										{
											imagepng($thumbimage, $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/thumbs/'.$images[$a]['filename'], $_quality_png);
										}
										imagedestroy($origimage);
										imagedestroy($thumbimage);
									}
								}
							}
						}
					}

					// Begrenzung der Bilder nicht größer als Anzahl vorhandener Bilder
					if ($limit AND (!$single OR !$single_gallery))
					{
						$noimage_rest = $noimage;
						if ($noimage > $limit_quantity)
						{
							$noimage = $limit_quantity;
						}
					}

					// Galerie mit einem Wort verbinden - erstes Bild im Array verlinken
					if ($word_gallery)
					{
						$noimage_rest = $noimage;
						$limit_quantity = 1;
						$noimage = 1;
					}

					// Textdatei einlesen und zuweisen
					if (isset($captions))
					{
						unset($captions);
					}
					$lang = JFactory::getLanguage();
					$captions_lang = $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/captions-'.$lang->getTag().'.txt';
					$captions_txtfile = $mosConfig_absolute_path.$rootfolder.$_images_dir_.'/captions.txt';

					if (file_exists($captions_lang))
					{
						$captions_file = file($captions_lang);
						$count = 0;

						foreach ($captions_file as $value)
						{
							$captions_line = explode('|', $value);
							$captions[$count] = $captions_line;
							$count++;
						}
					}
					elseif (file_exists($captions_txtfile) AND !file_exists($captions_lang))
					{
						$captions_file = file($captions_txtfile);
						$count = 0;

						foreach ($captions_file as $value)
						{
							$captions_line = explode('|', $value);
							$captions[$count] = $captions_line;
							$count++;
						}
					}

					// Schleife zum Anzeigen der Bilder - alle Bilder abarbeiten
					for ($a = 0; $a < $noimage; $a++)
					{
						if ($images[$a]['filename'] != '')
						{
							// Dateityp im Bildnamen entfernen
							$imagename = substr($images[$a]['filename'], 0, -4);
							$type = substr(strtolower($images[$a]['filename']),-3);
							$image_hash = $this->encrypt($encrypt,$imagename).'.'.$type;

							$captions_set = false;
							if (isset($captions))
							{
								foreach ($captions as $value)
								{
									if ($value[0] == $images[$a]['filename'])
									{
										$image_title = $value[1];
										if (isset($value[2]))
										{
											$image_description = $value[2];
										}
										else
										{
											$image_description = false;
										}
										$captions_set = true;
										break;
									}
								}
							}
							if (!$captions_set)
							{
								$image_title = $imagename;
								$image_description = false;
							}

							// Liste gesetzt - 1.5.11
							if ($list AND !$word_gallery)
							{
								$html .= '<li>';
							}
							elseif ($word_gallery)
							{
								$html .= '<span>';
							}
							else
							{
								$html .= '<span class="sige_cont_'.$_SESSION["sigcount"].'"><span class="sige_thumb">';
							}
							// Titel und Überschrift - IPTC aus Bild auslesen - 1.5-10
							if ($iptc == 1)
							{
								list($title_iptc, $caption_iptc) = $this->iptcinfo($a, $rootfolder, $_images_dir_, $images, $iptcutf8);
							}

							// Link zu einer URL - 1.5-14
							if ($image_link)
							{
								$html .= '<a href="http://'.$image_link.'" title="'.$image_link.'" ';
								if ($image_link_new)
								{
									$html .= 'target="_blank"';
								}
								$html .= '>';
							}
							// CSS Image Tooltip - 1.5-14
							elseif ($noslim AND $css_image)
							{
								$html .= '<a class="sige_css_image" href="#sige_thumbnail">';
							}
							// Slimbox-Effekt / Verlinkung
							elseif (!$noslim)
							{
								if ($watermark)
								{
										$html .= '<a href="'.$mosConfig_live_site.$rootfolder.$_images_dir_.'/wm/'.$image_hash.'"';
								}
								else
								{
										$html .= '<a href="'.$mosConfig_live_site.$rootfolder.$_images_dir_.'/'.$images[$a]['filename'].'"';
								}
								// CSS Image Tooltip - 1.5-14
								if ($css_image)
								{
									$html .= ' class="sige_css_image"';
								}

								// Prüfen, ob Verbindung der Bilder gewählt wurde und Ansicht wählen - 1.5-11
								if ($connect)
								{
									if ($view == 0) {$html .= ' rel="lightbox.sig'.$connect.'"';}
									elseif ($view == 1) {$html .= ' rel="lytebox.sig'.$connect.'"';}
									elseif ($view == 2) {$html .= ' rel="lyteshow.sig'.$connect.'"';}
									else {$html .= ' rel="shadowbox[sig'.$connect.']"';}
								}
								else
								{
									if ($view == 0) {$html .= ' rel="lightbox.sig'.$_SESSION["sigcount"].'"';}
									elseif ($view == 1) {$html .= ' rel="lytebox.sig'.$_SESSION["sigcount"].'"';}
									elseif ($view == 2) {$html .= ' rel="lyteshow.sig'.$_SESSION["sigcount"].'"';}
									else {$html .= ' rel="shadowbox[sig'.$_SESSION["sigcount"].']"';}
								}
								// Title-Tag - Anzeige in JS-Galerie
								$html .= ' title="';
								// Navigationstipp anzeigen
								if ($displaynavtip AND ($navtip != ''))
								{
									$html .= $navtip.'&lt;br /&gt;';
								}
								if ($displaymessage)
								{
									if ($message != '')
									{
										$html .= $message.': ';
									}
									$html .= '&lt;em&gt;'.$itemtitle.'&lt;/em&gt;&lt;br /&gt;';
								}
								if ($image_info)
								{
									//Title-Tag aus Bildnamen oder IPTC Werten - 1.5-10
									if ($iptc == 0)
									{
										$html .= '&lt;strong&gt;&lt;em&gt;'.$image_title.'&lt;/em&gt;&lt;/strong&gt;';
										if ($image_description)
										{
											$html .= ' - '.$image_description;
										}
									}
									else
									{
										$html .= '&lt;strong&gt;&lt;em&gt;'.$title_iptc.'&lt;/em&gt;&lt;/strong&gt; '.$caption_iptc;
									}
								}
								// Druckoption aktiviert - 1.5-10 -- Druckbutton eingefügt - 1.5-11
								if (($print == 1) AND ($iptc == 1))
								{
									if ($watermark)
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.php?img='.rawurlencode($mosConfig_live_site.$rootfolder.$_images_dir_.'/wm/'.$image_hash).'&amp;name='.rawurlencode($title_iptc).'&amp;caption='.rawurlencode($caption_iptc).'&quot;  title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
									}
									else
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.php?img='.rawurlencode($mosConfig_live_site.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']).'&amp;name='.rawurlencode($title_iptc).'&amp;caption='.rawurlencode($caption_iptc).'&quot;  title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
									}
								}
								elseif ($print == 1)
								{
									if ($watermark)
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.php?img='.rawurlencode($mosConfig_live_site.$rootfolder.$_images_dir_.'/wm/'.$image_hash).'&amp;name='.rawurlencode($image_title).'&quot;  title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
									}
									else
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.php?img='.rawurlencode($mosConfig_live_site.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']).'&amp;name='.rawurlencode($image_title).'&quot;  title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
									}
								}
								// Download 1.5.11
								if ($download == 1)
								{
									if ($watermark)
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/download.php?img='.rawurlencode($rootfolder.$_images_dir_.'/wm/'.$image_hash).'&quot;  title=&quot;Download&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/download.png&quot; /&gt;&lt;/a&gt;';
									}
									else
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/download.php?img='.rawurlencode($rootfolder.$_images_dir_.'/'.$images[$a]['filename']).'&quot;  title=&quot;Download&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/download.png&quot; /&gt;&lt;/a&gt;';
									}
								}
								$html .= '" >';
							}
							// Bilder anzeigen, wenn Liste oder Wortverlinkung nicht gewählt
							if (!$list AND !$word_gallery)
							{
								// Größe der Thumbnails festlegen
								$new_w = $_width_;
								// Berechnung der Proportionen des Bildes - oder gleich wie die Eingabe
								if ($ratio)
								{
									$imagedata = getimagesize($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);

									$new_h = (int)($imagedata[1]*($new_w/$imagedata[0]));
									if (($_height_) AND ($new_h > $_height_))
									{
										$new_h = $_height_;
										$new_w = (int)($imagedata[0]*($new_h/$imagedata[1]));
									}
								}
								else
								{
									// Proportionen nicht beibehalten, Größe überschreiben
									$new_h = $_height_;
								}

								// Vorschaubilderspeicherung gewählt
								if ($thumbs)
								{
									$html .= '<img alt="'.$image_title.'" title="';
									//Title-Tag aus Bildnamen oder IPTC Werten - 1.5-10
									if ($iptc == 0 OR ($title_iptc == '' AND $caption_iptc == ''))
									{
										$html .= $image_title;
									}
									else
									{
										$html .= $title_iptc.' '.$caption_iptc;
									}
									// Vorschaubild anzeigen
									if ($watermark)
									{
										$html .= '" src="'.$mosConfig_live_site.$rootfolder.$_images_dir_.'/thumbs/'.$image_hash.'" width="'.$new_w.'" height="'.$new_h.'" />';
									}
									else
									{
										$html .= '" src="'.$mosConfig_live_site.$rootfolder.$_images_dir_.'/thumbs/'.$images[$a]['filename'].'" width="'.$new_w.'" height="'.$new_h.'" />';
									}
								}
								else
								{
									// On-the-fly Ausgabe
									$html .= '<img alt="'.$image_title.'"  title="';
									//Title-Tag aus Bildnamen oder IPTC Werten - 1.5-10
									if ($iptc == 0 OR ($title_iptc == '' AND $caption_iptc == ''))
									{
										$html .= $image_title;
									}
									else
									{
										$html .= $title_iptc.' '.$caption_iptc;
									}
									// On-the-fly-Bild anzeigen
									if ($watermark)
									{
										$html .= '" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/showthumb.php?img='.$rootfolder.$_images_dir_.'/wm/'.$image_hash.'&amp;width='.$_width_.'&amp;height='.$_height_.'&amp;quality='.$_quality_.'&amp;ratio='.$ratio.'&amp;crop='.$crop.'&amp;crop_factor='.$crop_factor.'&amp;thumbdetail='.$thumbdetail.'" />';
									}
									else
									{
										$html .= '" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/showthumb.php?img='.$rootfolder.$_images_dir_.'/'.$images[$a]['filename'].'&amp;width='.$_width_.'&amp;height='.$_height_.'&amp;quality='.$_quality_.'&amp;ratio='.$ratio.'&amp;crop='.$crop.'&amp;crop_factor='.$crop_factor.'&amp;thumbdetail='.$thumbdetail.'" />';
									}
								}
							}
							elseif ($list AND !$word_gallery)
							{
								if ($iptc == 0 OR ($title_iptc == '' AND $caption_iptc == ''))
								{
									$html .= $image_title;
								}
								else
								{
									$html .= $title_iptc.' - '.$caption_iptc;
								}
							}
							elseif ($word_gallery)
							{
								$html .= $word_gallery;
							}

							// CSS Image Tooltip - 1.5-14
							if ($css_image AND !$image_link)
							{
								$html .= '<span>';
								if ($watermark)
								{
									$html .= '<img src="'.$mosConfig_live_site.$rootfolder.$_images_dir_.'/wm/'.$image_hash.'"';
								}
								else
								{
									$html .= '<img src="'.$mosConfig_live_site.$rootfolder.$_images_dir_.'/'.$images[$a]['filename'].'"';
								}

								if ($css_image_half AND !$list)
								{
									$imagedata = getimagesize($mosConfig_absolute_path.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']);
									$html .= ' width="'.($imagedata[0]/2).'" height="'.($imagedata[1]/2).'"';
								}

								if ($iptc == 0 OR ($title_iptc == '' AND $caption_iptc == ''))
								{
									$html .= ' title="'.$image_title.'"';
								}
								else
								{
									$html .= ' title="'.$title_iptc.' - '.$caption_iptc.'"';
								}
								$html .= ' /></span>';
							}

							// Verlinkung
							if (!$noslim OR $image_link OR $css_image)
							{
								$html .= '</a>';
							}
							// Bildunterschrift gesetzt - bei Liste deaktiviert
							if ($caption AND !$list AND !$word_gallery)
							{
								if ($iptc == 0)
								{
									$html .= '</span><span class="sige_caption">'.$image_title.'</span></span>';
								}
								else
								{
									$html .= '</span><span class="sige_caption">'.$title_iptc.'</span></span>';
								}
							}

							// Liste - 1.5-11
							if ($list AND !$word_gallery)
							{
								$html .= '</li>';
							}
							elseif ($word_gallery)
							{
								$html .= '</span>';
							}
							elseif (!$caption)
							{
								$html .= '</span></span>';
							}
						}

						// Zeilenumbruch - 1.5-14
						if ($column_quantity)
						{
							if (($a + 1) % $column_quantity == 0)
							{
								$html .= '<br class="sige_clr"/>';
							}
						}
					}	// Ende Bildschleife

					// Liste gesetzt - 1.5-11
					if ($list AND !$word_gallery)
					{
						$html .= '</ul>';
					}

					if (!$list AND !$word_gallery)
					{
						// Clear Both - Umfließen von Elementen beenden
						$html .="\n<span class=\"sige_clr\"></span>\n</span>\n";
					}

					// Limit gesetzt? Dann restliche Bilder einlesen (auch bei Einzelbild mit Galerie - 1.5-14)
					if ((($limit AND $limit_quantity < $noimage_rest) OR ($single AND $single_gallery) OR $word_gallery) AND (!$image_link OR !$css_image))
					{
						for ($a = $limit_quantity; $a < $noimage_rest; $a++)
						{
							if ($images[$a]['filename'] != '')
							{
								// Dateiendung entfernen in Zusatzbildern - 1.5-10
								$imagename = substr($images[$a]['filename'], 0, -4);
								$type = substr(strtolower($images[$a]['filename']),-3);
								$image_hash = $this->encrypt($encrypt,$imagename).'.'.$type;

								$captions_set = false;
								if (isset($captions))
								{
									foreach ($captions as $value)
									{
										if ($value[0] == $images[$a]['filename'])
										{
											$image_title = $value[1];
											if (isset($value[2]))
											{
												$image_description = $value[2];
											}
											else
											{
												$image_description = false;
											}
											$captions_set = true;
											break;
										}
									}
								}
								if (!$captions_set)
								{
									$image_title = $imagename;
									$image_description = false;
								}

								// Titel und Überschrift - IPTC aus Bild auslesen - 1.5-10
								if ($iptc == 1)
								{
									list($title_iptc, $caption_iptc) = $this->iptcinfo($a, $rootfolder, $_images_dir_, $images, $iptcutf8);
								}
								if ($watermark)
								{
									$html .= '<span style="display: none"><a href="'.$mosConfig_live_site.$rootfolder.$_images_dir_.'/wm/'.$image_hash.'"';
								}
								else
								{
									$html .= '<span style="display: none"><a href="'.$mosConfig_live_site.$rootfolder.$_images_dir_.'/'.$images[$a]['filename'].'"';
								}

								// Prüfen, ob Verbindung der Bilder gewählt wurde und Ansicht wählen - 1.5-11
								if ($connect)
								{
									if ($view == 0) { $html .= ' rel="lightbox.sig'.$connect.'"'; }
									elseif ($view == 1) { $html .= ' rel="lytebox.sig'.$connect.'"'; }
									elseif ($view == 2) { $html .= ' rel="lyteshow.sig'.$connect.'"'; }
									else { $html .= ' rel="shadowbox[sig'.$connect.']"'; }
								}
								else
								{
									if ($view == 0) { $html .= ' rel="lightbox.sig'.$_SESSION["sigcount"].'"'; }
									elseif ($view == 1) { $html .= ' rel="lytebox.sig'.$_SESSION["sigcount"].'"'; }
									elseif ($view == 2) { $html .= ' rel="lyteshow.sig'.$_SESSION["sigcount"].'"'; }
									else { $html .= ' rel="shadowbox[sig'.$_SESSION["sigcount"].']"'; }
								}
								$html .= ' title="';
								// Navigationstipp anzeigen
								if ($displaynavtip AND ($navtip != ''))
								{
									$html .= $navtip.'&lt;br /&gt;';
								}
								if ($displaymessage)
								{
									if ($message != '')
									{
										$html .= $message.': ';
									}
									$html .= '&lt;em&gt;'.$itemtitle.'&lt;/em&gt;&lt;br /&gt;';
								}
								if ($image_info)
								{
									//Title-Tag aus Bildnamen oder IPTC Werten - 1.5-10
									if ($iptc == 0)
									{
										$html .= '&lt;strong&gt;&lt;em&gt;'.$image_title.'&lt;/em&gt;&lt;/strong&gt;';
										if ($image_description)
										{
											$html .= ' - '.$image_description;
										}
									}
									else
									{
										$html .= '&lt;strong&gt;&lt;em&gt;'.$title_iptc.'&lt;/em&gt;&lt;/strong&gt; '.$caption_iptc;
									}
								}
								// Druckoption aktiviert - 1.5-10 -- Druckbutton eingefügt - 1.5-11
								if ($print == 1 AND $iptc == 1)
								{
									if ($watermark)
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.php?img='.rawurlencode($mosConfig_live_site.$rootfolder.$_images_dir_.'/wm/'.$image_hash).'&amp;name='.rawurlencode($title_iptc).'&amp;caption='.rawurlencode($caption_iptc).'&quot;  title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
									}
									else
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.php?img='.rawurlencode($mosConfig_live_site.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']).'&amp;name='.rawurlencode($title_iptc).'&amp;caption='.rawurlencode($caption_iptc).'&quot;  title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
									}
								}
								elseif ($print == 1)
								{
									if ($watermark)
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.php?img='.rawurlencode($mosConfig_live_site.$rootfolder.$_images_dir_.'/wm/'.$image_hash).'&amp;name='.rawurlencode($image_title).'&quot;  title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
									}
									else
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.php?img='.rawurlencode($mosConfig_live_site.$rootfolder.$_images_dir_.'/'.$images[$a]['filename']).'&amp;name='.rawurlencode($image_title).'&quot;  title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
									}
								}
								// Download 1.5.11
								if ($download == 1)
								{
									if ($watermark)
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/download.php?img='.rawurlencode($rootfolder.$_images_dir_.'/wm/'.$image_hash).'&quot;  title=&quot;Download&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/download.png&quot; /&gt;&lt;/a&gt;';
									}
									else
									{
										$html .= ' &lt;a href=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/download.php?img='.rawurlencode($rootfolder.$_images_dir_.'/'.$images[$a]['filename']).'&quot;  title=&quot;Download&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$mosConfig_live_site.'/plugins/content/plugin_sige/download.png&quot; /&gt;&lt;/a&gt;';
									}
								}
								$html .= '"></a></span>';
							}
						}
					}
					// Copyright anzeigen - 1.5-14
					if ($copyright AND !$single AND !$list AND !$word_gallery)
					{
						$html .= '<p class="sige_small"><a href="http://joomla-extensions.kubik-rubik.de/" title="SIGE Simple Image Gallery Extended - Joomla Erweiterung by Kubik-Rubik.de - Viktor Vogel" target="_blank">SIGE by Kubik-Rubik.de</a></p>';
					}
				}
				else
				{
					$html = '<strong>'.JText::_('NOIMAGES').'</strong><br /><br />'.JText::_('NOIMAGESDEBUG').' '.$mosConfig_live_site.$rootfolder.$_images_dir_;
				}
				// Ausgabe der Galerie
				$row->text = preg_replace("#{gallery}".$sige_code."{/gallery}#s", $html , $row->text);
			} // Ende - {gallery}

			// CSS und JS-Dateien in den Head-Bereich schreiben -- Shadowbox 1.5-11
			$mootools	= $pluginParams->get('mootools', 1);
			$js			= $pluginParams->get('js', 0);
			$lang = JFactory::getLanguage();
			$lang->getTag();

			$head = array();
			$head[] = "<style type='text/css'>\n".$sige_css."</style>";
			// JS nur einmal einlesen - beim ersten Artikel - 1.5-14
			if ($_SESSION["sigcountarticles"] == 0)
			{
				$head[] = '<link rel="stylesheet" href="'.$mosConfig_live_site.'/plugins/content/plugin_sige/sige.css" type="text/css" media="screen" />';
				if ($mootools) {
					$head[] = '<script type="text/javascript" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/mootools.js"></script>';
				}
				if ($js == 0)
				{
					if ($mootoolsupgrade)
					{
						if ($lang->getTag() == "de-DE")
						{
							$head[] = '<script type="text/javascript" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/slimbox2.js"></script>';
						}
						else
						{
							$head[] = '<script type="text/javascript" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/slimbox2_en.js"></script>';
						}
					}
					else
					{
						if ($lang->getTag() == "de-DE")
						{
							$head[] = '<script type="text/javascript" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/slimbox.js"></script>';
						}
						else
						{
							$head[] = '<script type="text/javascript" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/slimbox_en.js"></script>';
						}
					}
					$head[] = '<link rel="stylesheet" href="'.$mosConfig_live_site.'/plugins/content/plugin_sige/slimbox.css" type="text/css" media="screen" />';
				}
				if ($js == 1)
				{
					if ($lang->getTag() == "de-DE")
					{
						$head[] = '<script type="text/javascript" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/lytebox.js"></script>';
					}
					else
					{
						$head[] = '<script type="text/javascript" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/lytebox_en.js"></script>';
					}
					$head[] = '<link rel="stylesheet" href="'.$mosConfig_live_site.'/plugins/content/plugin_sige/lytebox.css" type="text/css" media="screen" />';
				}
				if ($js == 2)
				{
					if ($lang->getTag() == "de-DE")
					{
						$head[] = '<script type="text/javascript" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/shadowbox.js"></script>';
					}
					else
					{
						$head[] = '<script type="text/javascript" src="'.$mosConfig_live_site.'/plugins/content/plugin_sige/shadowbox_en.js"></script>';
					}
					$head[] = '<link rel="stylesheet" href="'.$mosConfig_live_site.'/plugins/content/plugin_sige/shadowbox.css" type="text/css" media="screen" />';
					$head[] = '<script type="text/javascript">Shadowbox.init();</script>';
				}
			}
			$head = "\n".implode("\n", $head)."\n";
			$document = &JFactory::getDocument();
			$document->addCustomTag($head);
		} // Ende Pluginsyntax
	} // Ende Funktion onPrepareContent

	// IPTC Funktion - Informationen aus dem Bild - 1.5-10
	function iptcinfo($f,$rootfolder,$_images_dir_,$images,$iptcutf8)
	{
		$info = array();
		$data = array();
		// Informationen des Bildes auslesen
		$size = getimagesize(JPATH_SITE.$rootfolder.$_images_dir_.'/'.$images[$f]['filename'], $info);
		// Prüfen, ob IPTC Werte gesetzt sind - 1.5-11-2
		if (isset($info['APP13']))
		{
			// IPTC auslesen
			$iptc_php = iptcparse($info['APP13']);

			if (is_array($iptc_php))
			{
				if (isset($iptc_php["2#120"][0]))
				{
					$data['caption'] = $iptc_php["2#120"][0];
				}
				else
				{
					$data['caption'] = '';
				}

				if (isset($iptc_php["2#005"][0]))
				{
					$data['title'] = $iptc_php["2#005"][0];
				}
				else
				{
					$data['title'] = '';
				}

				// IPTC UTF8-kodiert?
				if ($iptcutf8 == 1)
				{
					$iptctitle = html_entity_decode($data['title'], ENT_NOQUOTES);
					$iptccaption = html_entity_decode($data['caption'], ENT_NOQUOTES);
				}
				else
				{
					$iptctitle = utf8_encode(html_entity_decode($data['title'], ENT_NOQUOTES));
					$iptccaption = utf8_encode(html_entity_decode($data['caption'], ENT_NOQUOTES));
				}
			}
			else
			{
				$iptctitle = '';
				$iptccaption = '';
			}
		}
		else
		{
			$iptctitle = '';
			$iptccaption = '';
		}
		$ret = array($iptctitle, $iptccaption);
		return ($ret);
	}// Ende Funktion IPTC Daten auslesen

	// Funktionen für Sortierung nach Datum - Änderungsdatum der Bilddateien - 1.5-13
	function timeasc($a, $b)
	{
		return strcmp($a["timestamp"], $b["timestamp"]);
	}

	function timedesc($a, $b)
	{
		return strcmp($b["timestamp"], $a["timestamp"]);
	}// Ende Funktionen Sortierung nach Datum

	// Verschlüsselungsmethode anwenden
	function encrypt($encrypt,$imagename)
    {
		if ($encrypt == 0)
		{
			$image_hash = str_rot13($imagename);
		}
		elseif ($encrypt == 1)
		{
			$image_hash = md5($imagename);
		}
		elseif ($encrypt == 2)
		{
			$image_hash = sha1($imagename);
		}
		return ($image_hash);
    }
} // Ende Klasse plgContentSige
?>