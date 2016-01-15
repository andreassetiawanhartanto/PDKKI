<?php
/**
 *  @Copyright
 *  @package        SEO Friendly Links and Images
 *  @author         Viktor Vogel {@link http://www.kubik-rubik.de}
 *  @version        1.5-1
 *  @date           Created on 22-Nov-2011
 *  @link           Project Site {@link http://joomla-extensions.kubik-rubik.de/seofli-seo-friendly-links-and-images}
 *
 *  @license GNU/GPL
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');

class plgSystemSeofli extends JPlugin
{
    function __construct(&$subject, $config)
    {
        // Not in administration
        $app = JFactory::getApplication();

        if($app->isAdmin())
        {
            return;
        }

        parent::__construct($subject, $config);
        $this->loadLanguage('', JPATH_ADMINISTRATOR);
    }

    public function onAfterRender()
    {
        if($this->params->get('edit_links') OR $this->params->get('edit_images'))
        {
            $body = JResponse::getBody();

            // Edit links
            if($this->params->get('edit_links'))
            {
                preg_match_all('@<a(.*)>(.*)</a>@Uism', $body, $links);

                $count = 0;

                foreach($links[0] as $link)
                {
                    $link_new = '';

                    // Link is associated with an image - get image name
                    if(preg_match('@<img\s.*src=[\"\'](.*)[\"\'].*\s?/>@Uism', $link, $link_image))
                    {
                        $links[2][$count] = $this->getName($link_image[1]);
                    }

                    $links[2][$count] = strip_tags($links[2][$count]);

                    // No text linked - generate attribute from url
                    if(empty($links[2][$count]))
                    {
                        if(preg_match('@href=[\"\'](.*)[\"\']@Ui', $link, $link_href))
                        {
                            $links[2][$count] = $this->getName($link_href[1]);
                        }
                    }

                    if(preg_match('@title=[\"\'](.*)[\"\']@Ui', $link, $link_title_text))
                    {
                        if(empty($link_title_text[1]) OR $this->params->get('overwrite_links'))
                        {
                            $link_new = str_replace($link_title_text[0], 'title="'.trim($links[2][$count]).'"', $link);
                        }

                    }
                    else
                    {
                        $link_with_title = $links[1][$count].' title="'.trim($links[2][$count]).'"';
                        $link_new = str_replace($links[1][$count], $link_with_title, $link);
                    }

                    if(!empty($link_new))
                    {
                        $body = str_replace($link, $link_new, $body);
                    }

                    $count++;
                }

                unset($links);
            }

            // Edit images
            if($this->params->get('edit_images'))
            {
                preg_match_all('@<img\s(.*src=[\"\'](.*)[\"\'].*)\s?/>@Uism', $body, $images);

                $count = 0;

                foreach($images[0] as $image)
                {
                    $image_changed = false;

                    if(preg_match('@alt=[\"\'](.*)[\"\']@Ui', $image, $alt_text))
                    {
                        if(empty($alt_text[1]) OR $this->params->get('overwrite_images'))
                        {
                            $image_name = $this->getName($images[2][$count]);
                            $image_new = str_replace($alt_text[0], 'alt="'.$image_name.'"', $image);
                            $images[1][$count] = str_replace($alt_text[0], 'alt="'.$image_name.'"', $images[1][$count]);

                            $image_changed = true;
                        }
                    }
                    else
                    {
                        $image_name = $this->getName($images[2][$count]);
                        $image_with_alt = $images[1][$count].' alt="'.$image_name.'"';
                        $image_new = str_replace($images[1][$count], $image_with_alt, $image);
                        $images[1][$count] = $image_with_alt;

                        $image_changed = true;
                    }

                    // Title attribute for image
                    if($this->params->get('edit_images') == 2)
                    {
                        if($image_changed == false)
                        {
                            $image_new = $image;
                        }

                        if(preg_match('@title=[\"\'](.*)[\"\']@Ui', $image_new, $title_text))
                        {
                            if(empty($title_text[1]) OR $this->params->get('overwrite_images'))
                            {
                                $image_name = $this->getName($images[2][$count]);
                                $image_new = str_replace($title_text[0], 'title="'.$image_name.'"', $image_new);

                                $image_changed = true;
                            }
                        }
                        else
                        {
                            $image_name = $this->getName($images[2][$count]);
                            $image_with_title = $images[1][$count].' title="'.$image_name.'"';
                            $image_new = str_replace($images[1][$count], $image_with_title, $image_new);

                            $image_changed = true;
                        }
                    }

                    if($image_changed == true)
                    {
                        $body = str_replace($image, $image_new, $body);
                    }

                    $count++;
                }

                unset($images);
            }

            JResponse::setBody($body);
        }
    }

    // Generator the name for the attributes
    private function getName($image)
    {
        if(preg_match('@\?@', $image))
        {
            $image = substr($image, 0, strpos($image, "?"));
        }

        if(preg_match('@\.@', $image))
        {
            $image_name = (basename(substr($image, 0, strrpos($image, "."))));
        }
        else
        {
            $image_name = (basename($image));
        }

        $delete_chars = array('_', '-', '+', '=', '#');
        $image_name = ucwords(str_replace($delete_chars, ' ', $image_name));

        return $image_name;
    }
}
?>