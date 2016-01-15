<?php
/**
 * Plugin Helper File
 *
 * @package     Articles Anywhere
 * @version     1.13.1
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2011 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die();

// Import library dependencies
jimport( 'joomla.plugin.plugin' );

// Load common functions
require_once JPATH_PLUGINS.'/system/nnframework/helpers/functions.php';

/**
* Plugin that places articles
*/
class plgSystemArticlesAnywhereHelper
{
	function __construct( &$params, &$parameters )
	{
		$this->params = $params;
		$this->parameters = $parameters;
		$this->database	=& JFactory::getDBO();

		$this->params->comment_start = '<!-- START: Articles Anywhere -->';
		$this->params->comment_end = '<!-- END: Articles Anywhere -->';
		$this->params->message_start = '<!--  Articles Anywhere Message: ';
		$this->params->message_end = ' -->';

		$bts =	'((?:<p(?: [^>]*)?>)?)((?:\s*<br ?/?>)?\s*)';
		$bte =	'(\s*(?:<br ?/?>\s*)?)((?:</p>)?)';
		$this->params->regex = '#'
			.$bts.'\{'.preg_quote( $this->params->article_tag, '#' ).'(?: ([^\}]*))?\}'.$bte
			.'(.*?)'
			.$bts.'\{/'.preg_quote( $this->params->article_tag, '#' ).'\}'.$bte
			.'#s';
		$this->params->break_tags_start = $bts;
		$this->params->break_tags_end = $bte;

		$user =& JFactory::getUser();
		$this->params->aid = $user->getAuthorisedViewLevels();

		$query = 'SHOW COLUMNS FROM #__content';
		$this->database->setQuery( $query );
		$selects = $this->database->loadObjectList( 'Field' );
		if ( is_array( $selects ) ) {
			unset( $selects['introtext'] );
			unset( $selects['fulltext'] );
			$selects = array_keys( $selects );
			$this->params->dbselects_content = $selects;
		}

		$this->params->dbselects_k2 = 0;
		/*if ( file_exists( JPATH_ADMINISTRATOR.'/components/com_k2/admin.k2.php' ) ) {
			$query = 'SHOW COLUMNS FROM #__k2_items';
			$this->database->setQuery( $query );
			$selects = $this->database->loadObjectList( 'Field' );
			if ( is_array( $selects ) ) {
				unset( $selects['introtext'] );
				unset( $selects['fulltext'] );
				$selects = array_keys( $selects );
				$this->params->dbselects_k2 = $selects;
			}
		}*/

		$this->params->dispatcher = 0;
	}

////////////////////////////////////////////////////////////////////
// onContentPrepare
////////////////////////////////////////////////////////////////////

	function onContentPrepare ( &$article )
	{
		$message = '';

		if ( isset( $article->created_by ) && !empty( $this->params->articles_security_level ) && is_array( $this->params->articles_security_level ) && !in_array( '-1', $this->params->articles_security_level ) ) {
			// Lookup group level of creator
			$user_groups = new JAccess();
			$user_groups = $user_groups->getGroupsByUser( $article->created_by );

			// Set if security is passed
			// passed = creator is equal or higher than security group level
			$pass = 0;
			foreach( $user_groups as $group ) {
				if ( in_array( $group, $this->params->articles_security_level ) ) {
					$pass = 1;
					break;
				}
			}
			if ( !$pass ) {
				$message = JText::_( 'AA_OUTPUT_REMOVED_SECURITY' );
			}
		}

		if ( isset( $article->text ) ) {
			$this->processArticles( $article->text, $article, 'articles', $message );
		}
		if ( isset( $article->description ) ) {
			$this->processArticles( $article->description, $article, 'articles', $message );
		}
		if ( isset( $article->title ) ) {
			$this->processArticles( $article->title, $article, 'articles', $message );
		}
		if ( isset( $article->created_by_alias ) ) {
			$this->processArticles( $article->created_by_alias, $article, 'articles', $message );
		}
	}

////////////////////////////////////////////////////////////////////
// onAfterDispatch
////////////////////////////////////////////////////////////////////

	function onAfterDispatch()
	{
		$document =& JFactory::getDocument();
		$docType = $document->getType();

		if ( ( $docType == 'feed' || JRequest::getCmd( 'option' ) == 'com_acymailing' ) && isset( $document->items ) ) {
			$itemids = array_keys( $document->items );
			foreach ( $itemids as $i ) {
				$this->onContentPrepare( $document->items[$i] );
			}
		}

		if ( isset( $document->_buffer ) ) {
			$this->tagArea( $document->_buffer, 'ARTA', 'component' );
		}

		// PDF
		if ( $docType == 'pdf' ) {
			// Still to do for Joomla 1.6+
		}
	}

////////////////////////////////////////////////////////////////////
// onAfterRender
////////////////////////////////////////////////////////////////////
	function onAfterRender()
	{
		$document =& JFactory::getDocument();
		$docType = $document->getType();

		// not in pdf's
		if ( $docType == 'pdf' ) { return; }

		$html = JResponse::getBody();
		if ( $html == '' ) { return; }

		if ( $docType != 'html' ) {
			$this->replaceTags( $html );
			$this->cleanLeftoverJunk( $html );
		} else {
			if ( !( strpos( $html, '<body' ) === false ) && !( strpos( $html, '</body>' ) === false ) ) {
				$html_split = explode( '<body', $html, 2 );
				$body_split = explode( '</body>', $html_split['1'], 2 );

				// only do stuff in body
				$this->protect( $body_split['0'] );
				$this->replaceTags( $body_split['0'] );

				$html_split['1'] = implode( '</body>', $body_split );
				$html = implode( '<body', $html_split );
			} else {
				$this->protect( $html );
				$this->replaceTags( $html );
			}

			$this->cleanLeftoverJunk( $html );
			$this->unprotect( $html );

			// replace head with newly generated head
			// this is necessary because the plugins might have added scripts/styles to the head
			$orig_document = clone( $document );
			$this->updateHead( $html, $orig_document );
			unset( $orig_document );
		}

		JResponse::setBody( $html );
	}

	function replaceTags( &$str )
	{
		if ( $str == '' ) { return; }

		$document =& JFactory::getDocument();
		$docType = $document->getType();

		// COMPONENT
		if ( $docType == 'feed' || JRequest::getCmd( 'option' ) == 'com_acymailing' ) {
			$s = '#(<item[^>]*>)#s';
			$str = preg_replace( $s, '\1<!-- START: ARTA_COMPONENT -->', $str );
			$str = str_replace( '</item>', '<!-- END: ARTA_COMPONENT --></item>', $str );
		}
		if ( strpos( $str, '<!-- START: ARTA_COMPONENT -->' ) === false ) {
			$this->tagArea( $str, 'ARTA', 'component' );
		}

		$components = $this->params->components;
		if ( !is_array( $components ) ) {
			$components = explode( '|', $components );
		}

		$message = '';
		if ( in_array( JRequest::getCmd( 'option' ), $components ) ) {
			// For all components that are selected, set the meassage
			$message = JText::_( 'AA_OUTPUT_REMOVED_NOT_ENABLED' );
		}

		$components = $this->getTagArea( $str, 'ARTA', 'component' );

		$article = null;
		foreach ( $components as $component ) {
			$this->processArticles( $component['1'], $article, 'components', $message );
			$str = str_replace( $component['0'], $component['1'], $str );
		}

		// EVERYWHERE
		$this->processArticles( $str, $article, 'other' );
	}

	function tagArea( &$str, $ext = 'EXT', $area = '' )
	{
		if ( $area ) {
			if ( is_array( $str ) ) {
				foreach ( $str as $key => $val ) {
					$this->tagArea( $val, $ext, $area );
					$str[ $key ] = $val;
				}
			} else if ( $str ) {
				$str = '<!-- START: '.strtoupper( $ext ).'_'.strtoupper( $area ).' -->'.$str.'<!-- END: '.strtoupper( $ext ).'_'.strtoupper( $area ).' -->';
				if ( $area == 'article_text' ) {
					$str = preg_replace( '#(<hr class="system-pagebreak".*?/>)#si', '<!-- END: '.strtoupper( $ext ).'_'.strtoupper( $area ).' -->\1<!-- START: '.strtoupper( $ext ).'_'.strtoupper( $area ).' -->', $str );
				}
			}
		}
	}

	function getTagArea( &$str, $ext = 'EXT', $area = '' )
	{
		$matches = array();
		if ( $str && $area ) {
			$start = '<!-- START: '.strtoupper( $ext ).'_'.strtoupper( $area ).' -->';
			$end = '<!-- END: '.strtoupper( $ext ).'_'.strtoupper( $area ).' -->';
			$matches = explode( $start, $str );
			array_shift( $matches );
			foreach ( $matches as $i => $match ) {
				list( $text ) = explode( $end, $match, 2 );
				$matches[$i] = array(
					$start.$text.$end,
					$text
				);
			}
		}
		return $matches;
	}

	function processArticles( &$string, &$art, $area = 'articles', $message = '' )
	{
		if (
			$area == 'articles' && !$this->params->articles_enable ||
			$area == 'components' && !$this->params->components_enable ||
			$area == 'other' && !$this->params->other_enable
		) {
			$message = JText::_( 'AA_OUTPUT_REMOVED_NOT_ENABLED' );
		}

		if ( !( strpos( $string, '{/'.$this->params->article_tag.'}' ) === false ) ) {
			$regex = $this->params->regex;
			if ( @preg_match( $regex.'u', $string ) ) {
				$regex .= 'u';
			}

			$matches = array();
			$count = 0;
			while ( $count++ < 10 && !(strpos( $string, '{/'.$this->params->article_tag.'}' ) === false ) && preg_match_all( $regex, $string, $matches, PREG_SET_ORDER ) > 0 ) {
				foreach ( $matches as $match ) {
					$this->processMatch( $string, $art, $match, $message );
				}
				$matches = array();
			}
		}
	}

	function processMatch( &$string, &$art, &$match, &$message )
	{
		$html = '';
		if ( $message != '' ) {
			if ( $this->params->place_comments ) {
				$html = $this->params->comment_start.$this->params->message_start.$message.$this->params->message_end.$this->params->comment_end;
			}
		} else {
			/*
			p1_start	= $match['1'];
			br1a		= $match['2'];
			id			= $match['3'];
			br1b		= $match['4'];
			p1_end		= $match['5'];
			html		= $match['6'];
			p2_start	= $match['7'];
			br2a		= $match['8'];
			// end tag
			br2b		= $match['9'];
			p2_end		= $match['10'];
			*/

			$html = trim( $match['6'] );
			preg_match( '#^'.$this->params->break_tags_start.'(.*?)'.$this->params->break_tags_end.'$#s', trim( $html ), $text_match );

			if ( $match['1'] && ( $match['5'] || $text_match['1'] ) ) {
				$match['1'] = '';
			}
			if ( $match['10'] && ( $match['7'] || $text_match['5'] ) ) {
				$match['10'] = '';
			}

			if ( !(strpos( $string, '{/div}' ) === false ) && preg_match( '#^'.$this->params->break_tags_start.'(\{div[^\}]*\})'.$this->params->break_tags_end.'(.*?)'.$this->params->break_tags_start.'(\{/div\})'.$this->params->break_tags_end.'#s', $html, $div_match ) ) {
				if ( $div_match['1'] && $div_match['5'] ) {
					$div_match['1'] = '';
				}
				if ( $div_match['7'] && $div_match['11'] ) {
					$div_match['11'] = '';
				}
				$html = $div_match['2'].$div_match['3'].$div_match['4'].$div_match['1'].$div_match['6'].$div_match['11'].$div_match['8'].$div_match['9'].$div_match['10'];
			}

			$type = 'article';
			if ( !( strpos( $match['3'], ':' ) === false ) ) {
				$type = explode( ':', $match['3'], 2 );
				if ( $type['0'] == 'k2' ) {
					$match['3'] = trim( $type['1'] );
					$type = trim( $type['0'] );
				}
			}
			$html = $this->processArticle( $match['3'], $art, $html, $type );

			if ( $this->params->place_comments ) {
				$html = $this->params->comment_start.$html.$this->params->comment_end;
			}

			$html = $match['1'].$match['2'].$match['4'].$html.$match['8'].$match['9'].$match['10'];

			$html = preg_replace( '#((?:<p(?: [^>]*)?>\s*)?)((?:<br ?/?>)?\s*<div(?: [^>]*)?>.*?</div>\s*(?:<br ?/?>)?)((?:\s*</p>)?)#', '\3\2\1', $html );
			$html = preg_replace( '#(<p(?: [^>]*)?>\s*)<p(?: [^>]*)?>#', '\1', $html );
			$html = preg_replace( '#(</p>\s*)</p>#', '\1', $html );
		}

		$string = str_replace( $match['0'], $html, $string );
	}

	function processArticle( $article, $art, $text = '', $type = 'article' )
	{
		if ( $type == 'k2' && !$this->params->dbselects_k2 ) {
			$type = '';
		}
		$regex = '#\{(/?[^\}]+)\}#si';

		if ( preg_match_all( $regex, $text, $matches, PREG_SET_ORDER ) > 0 ) {
			if ( $type == 'k2' ) {
				$selects = $this->params->dbselects_k2;
			} else {
				$selects = $this->params->dbselects_content;
			}
			if ( $art && in_array( $article, array( 'current', 'self', '{id}', '{title}', '{alias}' ) ) ) {
				if ( isset( $art->id ) ) {
					$article = $art->id;
				} else if ( isset( $art->link ) && preg_match( '#&amp;id=([0-9]*)#', $art->link, $match ) ) {
					$article = $match['1'];
				}
			}

			foreach ( $matches as $match ) {
				$data = trim( $match['1'] );
				if ( !( strpos( $data, 'intro' ) === false ) ) {
					$selects[] = 'introtext';
				} else if ( !( strpos( $data, 'full' ) === false ) ) {
					$selects[] = 'fulltext';
				} else if ( !( strpos( $data, 'text' ) === false ) ) {
					$selects[] = 'introtext';
					$selects[] = 'fulltext';
				}
			}

			$selects = array_unique($selects);
			$selects = 'c.`'.implode( '`, c.`', $selects ).'`';

			$joins = '';
			if ( $type == 'article' ) {
				$selects .= ', CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as slug';
				$selects .= ', CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug';
				$joins = ' LEFT JOIN #__categories as cc ON cc.id = c.catid';
			}

			$where = ' AND ( c.title = '.$this->database->quote( NNFrameworkFunctions::html_entity_decoder( $article ) );
			$where .= ' OR c.alias = '.$this->database->quote( NNFrameworkFunctions::html_entity_decoder( $article ) );
			if ( is_numeric( $article ) ) {
				$where .= ' OR c.id = '.$article;
			}
			$where .= ' ) ';
			if ( !$this->params->ignore_state ) {
				$jnow =& JFactory::getDate();
				$now = $jnow->toMySQL();
				$nullDate = $this->database->getNullDate();
				if ( $type == 'k2' ) {
					$where .= ' AND c.published = 1 AND trash = 0';
				} else {
					$where .= ' AND c.state = 1';
				}
				$where .= ' AND ( c.publish_up = '.$this->database->quote( $nullDate ).' OR c.publish_up <= '.$this->database->quote( $now ).' )'
					.' AND ( c.publish_down = '.$this->database->quote( $nullDate ).' OR c.publish_down >= '.$this->database->quote( $now ).' )'
					;
			}

			$query = 'SELECT '.$selects
				.' FROM '.( $type == 'k2' ? '#__k2_items as c' : '#__content as c' )
				.$joins
				.' WHERE c.access IN ('.implode( ',', $this->params->aid ).')'
				.$where
				.' ORDER BY c.ordering'
				.' LIMIT 1';

			$this->database->setQuery( $query );
			$article = $this->database->loadObject();

			if ( !$article ) {
				return '<!-- '.JText::_( 'AA_ACCESS_TO_ARTICLE_DENIED' ).' -->';
			}

			$ifregex = '#\{if:([^\}]+)\}(.*?)(?:\{else\}(.*?))?\{/if\}#si';
			if ( preg_match_all( $ifregex, $text, $ifs, PREG_SET_ORDER ) > 0 ) {
				foreach ( $ifs as $if ) {
					$pass = 0;
					$eval = '$pass = ( ( $article->'.str_replace( '=', '==', trim( $if['1'] ) ).' ) ? 1 : 0 );';
					$eval = str_replace( '$article->!', '!$article->', $eval );
					eval( $eval );
					if ( !$pass ) {
						$text = str_replace( $if['0'], ( isset( $if['3'] ) ? $if['3'] : '' ), $text );
					} else {
						$text = str_replace( $if['0'], $if['2'], $text );
					}
				}
			}

		}

		if ( preg_match_all( $regex, $text, $matches, PREG_SET_ORDER ) > 0 ) {
			foreach ( $matches as $match ) {
				$data = trim( $match['1'] );
				$ok = 0;
				$str = '';
				$data = explode( ':', $data, 2 );
				$tag = trim( $data['0'] );
				$extra = isset( $data['1'] ) ? trim( $data['1'] ) : '';
				if ( $tag == '/link' ) {
					$str = '</a>';
					$ok = 1;
				} else if ( $tag == '/div' ) {
					$str = '</div>';
					$ok = 1;
				} else if ( $tag == 'div' || strpos( $tag, 'div ' ) === 0 ) {
					if ( $tag != 'div' ) {
						$extra = str_replace( 'div ', '', $tag ).':'.$extra;
					}

					$str = '';
					if ( $extra ) {
						$extra = explode( '|', $extra );
						$extras = new stdClass();
						foreach ( $extra as $e ) {
							if ( !( strpos( $e, ':' ) === false ) ) {
								list( $key, $val ) = explode( ':', $e, 2 );
								$extras->$key = $val;
							}
						}
						if ( isset( $extras->class ) ) {
							$str .= 'class="'.$extras->class.'"';
						}

						$style = array();
						if ( isset( $extras->width ) ) {
							if ( is_numeric( $extras->width ) ) {
								$extras->width .= 'px';
							}
							$style[] = 'width:'.$extras->width;
						}
						if ( isset( $extras->height ) ) {
							if ( is_numeric( $extras->height ) ) {
								$extras->height .= 'px';
							}
							$style[] = 'height:'.$extras->height;
						}
						if ( isset( $extras->align ) ) {
							$style[] = 'float:'.$extras->align;
						} else if ( isset( $extras->float ) ) {
							$style[] = 'float:'.$extras->float;
						}

						if ( !empty( $style ) ) {
							$str .= ' style="'.implode( ';', $style ).';"';
						}
					}
					$str = trim( '<div '.trim($str) ).'>';
					$ok = 1;
				} else if (
						( $tag == 'link' || $tag == 'url' )
					||	!( strpos( $tag, 'readmore' ) === false )
				) {
					if ( isset( $article->id ) ) {
						if ( $type == 'k2' ) {
							$link = 'index.php?option=com_k2&view=item&id='.$article->id;
							$component	=& JComponentHelper::getComponent( 'com_k2' );
							$menus		=& JApplication::getMenu( 'site', array() );
							$menuitems	= $menus->getItems( 'component_id', $component->id );
							$id = 0;
							if( is_array( $menuitems ) ) {
								foreach( $menuitems as $item ) {
									if ( @$item->query['view'] == 'item' && @$item->query['layout'] == 'item' && @$item->query['id'] == $article->id ) {
										$id = $item->id;
										break;
									}
								}
								if ( !$id ) {
									foreach( $menuitems as $item ) {
										if ( @$item->query['view'] == 'itemlist' && @$item->query['layout'] == 'category' && @$item->query['id'] == $article->catid ) {
											$id = $item->id;
											break;
										}
									}
								}
							}
						} else {
							$slug = 'id='.$article->slug;
							if ( $article->catid ) {
								$slug .= '&catid='.$article->catslug;
							}
							$link = 'index.php?option=com_content&view=article&'.$slug;
							$component	=& JComponentHelper::getComponent( 'com_content' );
							$menus		=& JApplication::getMenu( 'site', array() );
							$menuitems	= $menus->getItems( 'component_id', $component->id );
							$id = 0;
							if( is_array( $menuitems ) ) {
								foreach( $menuitems as $item ) {
									if ( @$item->query['view'] == 'article' && @$item->query['id'] == $article->id ) {
										$id = $item->id;
										break;
									}
								}
								if ( !$id ) {
									foreach( $menuitems as $item ) {
										if ( @$item->query['view'] == 'category' && @$item->query['id'] == $article->catid ) {
											$id = $item->id;
											break;
										}
									}
								}

								if ( !$id ) {
									foreach( $menuitems as $item ) {
										if ( @$item->query['view'] == 'section' && @$item->query['id'] == $article->sectionid ) {
											$id = $item->id;
											break;
										}
									}
								}
							}
						}
						if ( $id ) {
							$link .= '&Itemid='.$id;
						}
						$link = JRoute::_( $link );

						if ( $tag == 'link' ) {
							$str = '<a href="'.$link.'">';
						} else if ( $tag == 'url' ) {
							$str = $link;
						} else {
							$readmore = JText::_( 'COM_CONTENT_READ_MORE_TITLE' );
							$class = 'readon';

							if ( $extra ) {
								$extra = explode( '|', $extra );
								if ( trim( $extra['0'] ) ) {
									$readmore = JText::sprintf( trim( $extra['0'] ), $article->title );
									if ( !$readmore ) {
										$readmore = $extra['0'];
									}
								}
								if ( isset( $extra['1'] ) ) {
									$class = trim( $extra['1'] );
								}
							}

							$str = '<a href="'.$link.'" class="'.$class.'">'.$readmore.'</a>';
						}
						$ok = 1;
					}
				} else if (
						!( strpos( $tag, 'text' ) === false )
					||	!( strpos( $tag, 'intro' ) === false )
					||	!( strpos( $tag, 'full' ) === false )
				) {
					// TEXT data
					$article->text = '';

					if ( !( strpos( $tag, 'intro' ) === false ) ) {
						if ( isset( $article->introtext ) ) {
							$article->text = $article->introtext;
							$ok = 1;
						}
					} else if ( !( strpos( $tag, 'full' ) === false ) ) {
						if ( isset( $article->fulltext ) ) {
							$article->text = $article->fulltext;
							$ok = 1;
						}
					} else if ( !( strpos( $tag, 'text' ) === false ) ) {
						if ( isset( $article->introtext ) && isset( $article->fulltext ) ) {
							$article->text = $article->introtext.$article->fulltext;
							$ok = 1;
						}
					}

					$article->parameters = new JRegistry;
					if ( $type == 'k2' ) {
						$article->parameters->loadINI( $article->params );
					} else {
						$article->parameters->loadString( $article->attribs );
					}

					if ( $this->params->run_content_plugins ) {
						if ( !$this->params->dispatcher ) {
							$plugins = JPluginHelper::getPlugin( 'system' );
							$plugins = array_merge( $plugins, JPluginHelper::getPlugin( 'content' ) );
							$plugins = array_merge( $plugins, JPluginHelper::getPlugin( 'k2' ) );
							foreach ( $plugins as $plugin )	{
								JPluginHelper::importPlugin( $plugin->type, $plugin->name );
							}
							$this->params->dispatcher = clone( JDispatcher::getInstance() );
							$this->params->dispatcher->detach( 'plgSystemArticlesAnywhere' );
							$this->params->dispatcher->detach( 'plgSystemBetterPreview' );
						}
						$this->params->dispatcher->trigger( 'onPrepareContent', array( &$article, &$article->parameters, 0 ) );
					}

					$str = $article->text;

					if ( $extra ) {
						$attribs = explode( ':', $extra );

						$max = 0;
						$strip = 0;
						foreach ( $attribs as $attrib ) {
							if ( trim( $attrib ) == 'strip' ) {
								$strip = 1;
							} else {
								$max = $attrib;
							}
						}

						$word_limit = ( !( strpos( $max, 'word' ) === false ) );
						if ( $strip ) {
							// remove pagenavcounter
							$str = preg_replace( '#(<'.'div class="pagenavcounter">.*?</div>)#si', ' ', $str );
							// remove pagenavbar
							$str = preg_replace( '#(<'.'div class="pagenavbar">(<div>.*?</div>)*</div>)#si', ' ', $str );
							// remove scripts
							$str = preg_replace( '#(<'.'script[^a-z0-9].*?</script>)#si', ' ', $str );
							$str = preg_replace( '#(<'.'noscript[^a-z0-9].*?</noscript>)#si', ' ', $str );
							// remove other tags
							$str = preg_replace( '#(<'.'/?[a-z][a-z0-9]?.*?>)#si', ' ', $str );
							// remove double whitespace

							$str = trim( preg_replace( '#\s+#s', ' ', $str ) );
							if ( $max ) {
								$orig_len = strlen( $str );
								if ( $word_limit ) {
									// word limit
									$str = trim( preg_replace( '#^(([^\s]+\s*){'.(int) $max.'}).*$#s', '\1', $str ) );
									if ( strlen( $str ) < $orig_len ) {
										if ( preg_match( '#[^a-z0-9]$#si', $str ) ) {
											$str .= ' ';
										}
										if ( $this->params->use_ellipsis ) {
											$str .= '...';
										}
									}
								} else {
									// character limit
									$max = (int) $max;
									if ( $max < $orig_len ) {
										$str = rtrim( substr( $str, 0, ( $max-3 ) ) );
										if ( preg_match( '#[^a-z0-9]$#si', $str ) ) {
											$str .= ' ';
										}
										if ( $this->params->use_ellipsis ) {
											$str .= '...';
										}
									}
								}
							}
						} else if ( $max && ( $word_limit || (int) $max < strlen( $str ) ) ) {
							$max = (int) $max;

							// store pagenavcounter & pagenav (exclude from count)
							preg_match( '#<'.'div class="pagenavcounter">.*?</div>#si', $str, $pagenavcounter );
							$pagenavcounter = isset( $pagenavcounter['0'] ) ? $pagenavcounter['0'] : '';
							if ( $pagenavcounter ) {
								$str = str_replace( $pagenavcounter, '<!-- ARTA_PAGENAVCOUNTER -->', $str );
							}
							preg_match( '#<'.'div class="pagenavbar">(<div>.*?</div>)*</div>#si', $str, $pagenav );
							$pagenav = isset( $pagenav['0'] ) ? $pagenav['0'] : '';
							if ( $pagenav ) {
								$str = str_replace( $pagenav, '<!-- ARTA_PAGENAV -->', $str );
							}

							// add explode helper strings around tags
							$explode_str = '<!-- ARTA_TAG -->';
							$str = preg_replace( '#(<\/?[a-z][a-z0-9]?.*?>|<!--.*?-->)#si', $explode_str.'\1'.$explode_str, $str );

							$str_array = explode( $explode_str, $str );

							$str = array();
							$tags = array();
							$count = 0;
							$is_script = 0;
							foreach ( $str_array as $i => $str_part ) {
								if ( fmod( $i, 2 ) ) {
									// is tag
									$str[] = $str_part;
									preg_match( '#^<(\/?([a-z][a-z0-9]*))#si', $str_part, $tag );
									if ( !empty( $tag ) ) {
										if ( $tag['1'] == 'script' ) {
											$is_script = 1;
										}

										if (	!$is_script
												// only if tag is not a single html tag
											&&	( strpos( $str_part, '/>' ) === false )
												// just in case single html tag has no closing character
											&& 	!in_array( $tag['2'], array( 'area', 'br', 'hr', 'img', 'input', 'param' ) )
										) {
											$tags[] = $tag['1'];
										}

										if ( $tag['1'] == '/script' ) {
											$is_script = 0;
										}
									}
								} else if ( $is_script ) {
									$str[] = $str_part;
								} else {
									if ( $word_limit ) {
										// word limit
										if ( $str_part ) {
											$words = explode( ' ', trim( $str_part ) );
											$word_count = count( $words );
											if ( $max < ( $count + $word_count ) ) {
												$words_part = array();
												$word_count = 0;
												foreach( $words as $word ) {
													if ( $word ) {
														$word_count++;
													}
													if ( $max < ( $count + $word_count ) ) {
														break;
													}
													$words_part[] = $word;
												}
												$string = rtrim( implode( ' ', $words_part ) );
												if ( preg_match( '#[^a-z0-9]$#si', $string ) ) {
													$string .= ' ';
												}
												if ( $this->params->use_ellipsis ) {
													$string .= '...';
												}
												$str[] = $string;
												break;
											}
											$count += $word_count;
										}
										$str[] = $str_part;
									} else {
										// character limit
										if ( $max < ( $count + strlen( $str_part ) ) ) {
											// strpart has to be cut off
											$maxlen = $max-$count;
											if ( $maxlen < 3 ) {
												$string = '';
												if ( preg_match( '#[^a-z0-9]$#si', $str_part ) ) {
													$string .= ' ';
												}
												if ( $this->params->use_ellipsis ) {
													$string .= '...';
												}
												$str[] = $string;
											} else {
												$string = rtrim( substr( $str_part, 0, ( $maxlen-3 ) ) );
												if ( preg_match( '#[^a-z0-9]$#si', $string ) ) {
													$string .= ' ';
												}
												if ( $this->params->use_ellipsis ) {
													$string .= '...';
												}
												$str[] = $string;
											}
											break;
										}
										$count += strlen( $str_part );
										$str[] = $str_part;
									}
								}
							}

							// revers sort open tags
							krsort( $tags );
							$tags = array_values( $tags );
							$count = count( $tags );

							for( $i = 0; $i < 3; $i++ ) {
								foreach( $tags as $ti => $tag ) {
									if( $tag['0'] == '/' ) {
										for( $oi = $ti+1; $oi < $count; $oi++ ) {
											$opentag = $tags[$oi];
											if ( $opentag == $tag ) {
												break;
											}
											if ( '/'.$opentag == $tag ) {
												unset( $tags[$ti] );
												unset( $tags[$oi] );
												break;
											}
										}
									}
								}
							}

							foreach ( $tags as $tag ) {
								// add closing tag to end of string
								if( $tag['0'] != '/' ) {
									$str[] = '</'.$tag.'>';
								}
							}
							$str = implode( '', $str );

							$str = str_replace( array( '<!-- ARTA_PAGENAVCOUNTER -->', '<!-- ARTA_PAGENAV -->' ), array( $pagenavcounter, $pagenav ), $str );
						}
					}

					if ( $art && isset( $art->id ) && $art->id ) {
						$str = str_replace( 'view=article&amp;id='.$art->id, 'view=article&amp;id='.$article->id, $str );
					}

				} else {
					// Get data from db columns
					if ( isset( $article->$tag ) ) {
						$str = $article->$tag;
						$ok = 1;
					}
					// otherwise get data from parameter data
					if ( !$ok ) {
						$params = new stdClass();
						if ( isset( $article->attribs ) ) {
							$params = $this->parameters->getParams( $article->attribs );
						} else if ( isset( $article->params ) ) {
							$params = $this->parameters->getParams( $article->params );
						}
						if ( $params && isset( $params->$tag ) ) {
							$str = $params->$tag;
							$ok = 1;
						}
					}
					// otherwise get data from extra fields (for k2 items)
					if ( !$ok && $type == 'k2' ) {
						$extravalue = $this->getExtraFieldValue( $article->extra_fields, $tag, $article->catid );
						if ( $extravalue !== null ) {
							$str = $extravalue;
							$ok = 1;
						}
					}

					if ( $ok
						&& !( strpos( $str, '-' ) == false )
						&& !preg_match( '#[a-z]#i', $str )
						&& strtotime( $str )
					) {
						if ( $extra && !( strpos( $extra, '%' ) === false ) ) {
							$extra = NNFrameworkFunctions::dateToDateFormat( $extra );
						}
						$str = JHTML::_( 'date', strtotime( $str ), $extra );
					}
				}

				if ( $ok ) {
					$text = str_replace( $match['0'], $str, $text );
				}
			}
		}

		return $text;
	}

	/*
	 * Retrieve data from k2 extra fields
	 */
	function getExtraFieldValue( &$extra, $data, $catid )
	{
		$value = null;

		$db =& JFactory::getDBO();
		$query = "SELECT extraFieldsGroup FROM #__k2_categories WHERE id = ".(int) $catid." LIMIT 1";
		$db->setQuery($query);
		$extragroup = $db->loadResult();
		if ( !$extragroup ) {
			return $value;
		}

		require_once JPATH_ADMINISTRATOR.'/components/com_k2/lib/JSON.php';
		$json = new Services_JSON;

		$where = 'name = '.$db->quote( $data );
		if ( substr( $data, 0, 6 ) == 'extra-' && is_numeric( substr( $data, 6 ) ) ) {
			$where = "( id = ".(int) substr( $data, 6 )." OR ".$where." )";
		}

		$query = "SELECT * FROM #__k2_extra_fields
			WHERE `group` = ".(int) $extragroup."
			AND ".$where."
			AND published = 1
			LIMIT 1";
		$db->setQuery($query);
		$extrafield = $db->loadObject();
		if ( !$extrafield ) {
			return $value;
		}

		$fielddata = $json->decode( $extra );
		foreach ( $fielddata as $field ) {
			if ( $field->id == $extrafield->id ) {
				if ( $field->value != '' ) {
					$value = $field->value;
					if ( $extrafield->type == 'textfield' || $extrafield->type == 'textarea' || $extrafield->type == 'csv' ) {
						return $value;
					} else if ( $extrafield->type == 'link' && is_array( $field->value ) ) {
						$link = new stdClass();
						$link->name = isset( $field->value['0'] ) ? $field->value['0'] : '';
						$link->value = isset( $field->value['1'] ) ? $field->value['1'] : '';
						$link->target = isset( $field->value['2'] ) ? $field->value['2'] : '';
						return $this->getFieldLink( $link );
					}
				}
				break;
			}
		}

		$defaultdata = $json->decode( $extrafield->value );
		if ( $value !== null ) {
			$v = '';
			foreach( $defaultdata as $defaultvalue ) {
				if ( $value == $defaultvalue->value ) {
					$v = $defaultvalue->name;
				}
			}
			$value = $v;
		} else {
			if ( isset( $defaultdata['0'] ) ) {
				$value = '';
				if ( $extrafield->type == 'textfield' || $extrafield->type == 'textarea' || $extrafield->type == 'csv' ) {
					$value = $defaultdata['0']->value;
				} else if ( $extrafield->type == 'link' ) {
					$value = $this->getFieldLink( $defaultdata['0'] );
				} else if ( $extrafield->type != 'multipleSelect' ) {
					$value = $defaultdata['0']->name;
				}
			}
		}
		return $value;
	}

	function getExtraFieldInfo( $id ){
		$db =& JFactory::getDBO();
		$query= "SELECT * FROM #__k2_extra_fields WHERE published = 1 AND id = ".(int) $id." LIMIT 1";
		$db->setQuery( $query );
		return $db->loadObject();
	}

	function getFieldLink( &$field ){
		if ( !$field->value || $field->value == 'http://' ) {
			return $field->name;
		}
		$params =& JComponentHelper::getParams('com_k2');

		switch ( $field->target ){
			case 'same':
			default:
				$attributes='';
				break;

			case 'new':
				$attributes='target="_blank"';
				break;

			case 'popup':
				$attributes='class="classicPopup" rel="{x:'.$params->get('linkPopupWidth').',y:'.$params->get('linkPopupHeight').'}"';
				break;

			case 'lightbox':
				$filename = @basename( $field->value );
				$extension = JFile::getExt( $filename );
				$imgExtensions = array( 'jpg','jpeg','gif','png' );
				if ( !empty( $extension ) && in_array( $extension, $imgExtensions )) {
					$attributes='class="modal"';
				}
				else {
					$attributes='class="modal" rel="{handler:\'iframe\',size:{x:'.$params->get('linkPopupWidth').',y:'.$params->get('linkPopupHeight').'}}"';
				}
				break;

		}
		return '<a href="'.$field->value.'" '.$attributes.'>'.$field->name.'</a>';
	}

	/*
	 * Protect input and text area's
	 */
	function addParagraphTags( &$string, $p_start = '', $p_end = '' )
	{
		$str = trim( preg_replace( '#<\!--.*?-->#si', '', $string ) );

		if ( $str == '' ) {
			return;
		}

		// if there is a starting p tag
		if ( $p_start ) {
			$p_match = '#<p( |\s|>)#si';
			// add starting p tag if content has no starting p tag
			// or if ending p tag appears before starting p tag
			if (
					!( preg_match( $p_match, $str ) )
				||	(
						!( stripos( $str, '</p>' ) === false )
						&& stripos( $str, '</p>' ) < stripos( $str, '<p' )
					)
			) {
				$string = $p_start.$string;
			}
		}
		// if there is a ending p tag
		if ( $p_end ) {
			// add ending p tag if content has no ending p tag
			// or if starting p tag appears later than ending p tag
			if (
					stripos( $str, '</p>' ) === false
				||	strripos( $str, '</p>' ) < strripos( $str, '<p' )
			) {
				$string .= $p_end;
			}
		}
	}

	/*
	 * Protect input and text area's
	 */
	function protect( &$string )
	{
		if (	in_array( JRequest::getCmd( 'task' ), array( 'edit' ) )
			||	in_array( JRequest::getCmd( 'view' ), array( 'edit', 'form' ) )
			||	in_array( JRequest::getCmd( 'layout' ), array( 'edit', 'form', 'write' ) )
			||	in_array( JRequest::getCmd( 'option' ), array( 'com_contentsubmit', 'com_cckjseblod' ) )
		) {
			// Protect complete adminForm (to prevent articles from being created when editing articles and such)
			$unprotected = '{'.$this->params->article_tag;
			$protected = $this->protectStr( $unprotected );
			$string = preg_replace( '#(<'.'form [^>]*(id|name)="adminForm")#si', '<!-- TMP_START_EDITOR -->\1', $string );
			$string = explode( '<!-- TMP_START_EDITOR -->', $string );
			foreach ( $string as $i => $str ) {
				if ( !empty( $str ) != '' && fmod( $i, 2 ) ) {
					if ( !( strpos( $str, $unprotected ) === false ) ) {
						$str = explode( '</form>', $str, 2 );
						$str['0'] = str_replace( $unprotected, $protected, $str['0'] );
						$string[$i] = implode( '</form>', $str );
					}
				}
			}
			$string = implode( '', $string );
		}
	}

	function unprotect( &$string )
	{
		$string = str_replace( $this->protectStr( '{'.$this->params->article_tag ), '{'.$this->params->article_tag, $string );
	}

	function protectStr( $string )
	{
		$string = base64_encode( $string );
		return $string;
	}

	function cleanLeftoverJunk( &$str )
	{
		if ( !(strpos( $str, '{/'.$this->params->article_tag.'}' ) === false ) ) {
			$regex = $this->params->regex;
			if ( @preg_match( $regex.'u', $str ) ) {
				$regex .= 'u';
			}
			if( preg_match( $regex, $str ) ) {
				$str = preg_replace( $regex, '', $str );
			}
		}
		$str = preg_replace( '#<\!-- (START|END): ARTA_[^>]* -->#', '', $str );
		if ( !$this->params->place_comments ) {
			$str = str_replace( array(
					$this->params->comment_start, $this->params->comment_end,
					htmlentities( $this->params->comment_start ), htmlentities( $this->params->comment_end ),
					urlencode( $this->params->comment_start ), urlencode( $this->params->comment_end )
				), '', $str );
			$str = preg_replace( '#'.preg_quote( $this->params->message_start, '#' ).'.*?'.preg_quote( $this->params->message_end, '#' ).'#', '', $str );
		}
	}

	function updateHead( &$html, &$orig_document )
	{
		if ( strpos( $html, '</head>' ) === false ) {
			return;
		}
		$document =& JFactory::getDocument();

		// get line endings
		$lnEnd = $document->_getLineEnd();
		$tab = $document->_getTab();
		$tagEnd	= ' />';
		$str = '';

		// Generate link declarations
		foreach ( $document->_links as $link ) {
			if ( !in_array( $link, $orig_document->_links ) ) {
				$str .= $tab.$link.$tagEnd.$lnEnd;
			}
		}

		// Generate stylesheet links
		foreach ($document->_styleSheets as $strSrc => $strAttr ) {
			if ( !array_key_exists( $strSrc, $orig_document->_styleSheets ) ) {
				$str .= $tab . '<link rel="stylesheet" href="'.$strSrc.'" type="'.$strAttr['mime'].'"';
				if (!is_null($strAttr['media'])){
					$str .= ' media="'.$strAttr['media'].'" ';
				}
				$temp = JArrayHelper::toString($strAttr['attribs']);
				if ( $temp ) {
					$str .= ' '.$temp;;
				}
				$str .= $tagEnd.$lnEnd;
			}
		}

		// Generate stylesheet declarations
		foreach ($document->_style as $type => $content) {
			if ( !in_array( $content, $orig_document->_style ) ) {
				$str .= $tab.'<style type="'.$type.'">'.$lnEnd;

				// This is for full XHTML support.
				if ($document->_mime == 'text/html' ) {
					$str .= $tab.$tab.'<!--'.$lnEnd;
				} else {
					$str .= $tab.$tab.'<![CDATA['.$lnEnd;
				}

				$str .= $content . $lnEnd;

				// See above note
				if ($document->_mime == 'text/html' ) {
					$str .= $tab.$tab.'-->'.$lnEnd;
				} else {
					$str .= $tab.$tab.']]>'.$lnEnd;
				}
				$str .= $tab.'</style>'.$lnEnd;
			}
		}

		// Generate script file links
		foreach ($document->_scripts as $strSrc => $strType) {
			if ( !array_key_exists( $strSrc, $orig_document->_scripts ) ) {
				$str .= $tab.'<script type="'.$strType.'" src="'.$strSrc.'"></script>'.$lnEnd;
			}
		}

		// Generate script declarations
		foreach ($document->_script as $type => $content) {
			if ( !in_array( $content, $orig_document->_script ) ) {
				$str .= $tab.'<script type="'.$type.'">'.$lnEnd;

				// This is for full XHTML support.
				if ($document->_mime != 'text/html' ) {
					$str .= $tab.$tab.'<![CDATA['.$lnEnd;
				}

				$str .= $content.$lnEnd;

				// See above note
				if ($document->_mime != 'text/html' ) {
					$str .= $tab.$tab.'// ]]>'.$lnEnd;
				}
				$str .= $tab.'</script>'.$lnEnd;
			}
		}

		foreach($document->_custom as $custom) {
			if ( !in_array( $custom, $orig_document->_custom ) ) {
				$str .= $tab.$custom.$lnEnd;
			}
		}

		JResponse::setBody( str_replace( '</head>', $str."\n".'</head>', JResponse::getBody() ) );
	}
}