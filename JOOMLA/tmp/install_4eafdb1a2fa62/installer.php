<?php
/**
 * Installer File
 * Performs an install / update of NoNumber! extensions
 *
 * @package     NoNumber!-installer
 * @version     11.10.5
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2011 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die();

$mainframe =& JFactory::getApplication();
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

define( 'JV15', ( version_compare( JVERSION, '1.6.0', 'l' ) ) );

$jv = JV15 ? '15' : '16';
$comp_folder = dirname( __FILE__ );

require_once $comp_folder.'/installer/helper_'.$jv.'.php';

$mainframe =& JFactory::getApplication();
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

// Install the Installer languages
installLanguages( $comp_folder.'/installer/language', 1, 0 );

// Load language for messaging
$lang =& JFactory::getLanguage();
if ( $lang->getTag() != 'en-GB' ) {
	// Loads English language file as fallback (for undefined stuff in other language file)
	$lang->load( 'com_nonumber-installer-uninstallme', JPATH_ADMINISTRATOR, 'en-GB' );
}
$lang->load( 'com_nonumber-installer-uninstallme', JPATH_ADMINISTRATOR, null, 1 );

$install_file = $comp_folder.'/extensions.php';
if ( !JFile::exists( $install_file ) || !is_readable( $install_file ) ) {
	$mainframe->enqueueMessage( JText::sprintf( 'NNI_CANNOT_READ_THE_REQUIRED_INSTALLATION_FILE', $install_file ), 'error' );
	uninstallInstaller();
} else if ( !JFolder::exists( $comp_folder.'/extensions/'.$jv ) ) {
	$mainframe->enqueueMessage( JText::sprintf( 'NNI_NOT_COMPATIBLE', round( JVERSION, 1 ) ), 'error' );
	uninstallInstaller();
}

// Create database object
$db =& JFactory::getDBO();

$states = array();
$has_installed = 0;
$has_updated = 0;

$ext = 'NNI_THE_EXTENSION'; // default value. Will be overruled in extensions.php
require_once $install_file;

if ( is_array( $states ) ) {
	foreach ( $states as $state ) {
		if ( !$state ) {
			$has_installed = $has_updated = 0;
			break;
		} else if ( $state == 2 ) {
			$has_updated = 1;
		} else {
			$has_installed = 1;
		}
	}
}

if ( !$has_installed && !$has_updated ) {
	$mainframe->enqueueMessage( JText::_( 'NNI_SOMETHING_HAS_GONE_WRONG_DURING_INSTALLATION_OF_THE_DATABASE_RECORDS' ), 'error' );
	uninstallInstaller();
}

if ( !installFiles( $comp_folder.'/extensions' ) ) {
	$mainframe->enqueueMessage( JText::_( 'NNI_COULD_NOT_COPY_ALL_FILES' ), 'error error_nonumber' );
	uninstallInstaller();
}

$txt_installed = ( $has_installed ) ? JText::_( 'NNI_INSTALLED' ) : '';
$txt_installed .= ( $has_installed && $has_updated ) ? ' / ' : '';
$txt_installed .= ( $has_updated ) ? JText::_( 'NNI_UPDATED' ) : '';
$mainframe->set( '_messageQueue', '' );
$mainframe->enqueueMessage( sprintf( JText::_( 'NNI_THE_EXTENSION_HAS_BEEN_INSTALLED_SUCCESSFULLY' ), JText::_( $ext ), $txt_installed ), 'message' );
$mainframe->enqueueMessage( JText::_( 'NNI_PLEASE_CLEAR_YOUR_BROWSERS_CACHE' ), 'notice' );

installFramework( $comp_folder );

uninstallInstaller();

/* FUNCTIONS */

/**
 * Copies language files to the language folders
 */
function installLanguages( $folder, $force = 1, $all = 1, $break = 1 ) {
	if ( JFolder::exists( $folder.'/admin' ) ) {
		$path = JPATH_ADMINISTRATOR.'/language';
		if ( !installLanguagesByPath( $folder.'/admin', $path, $force, $all, $break ) && $break ) {
			return 0;
		}
	}
	if ( JFolder::exists( $folder.'/site' ) ) {
		$path = JPATH_SITE.'/language';
		if ( !installLanguagesByPath( $folder.'/site', $path, $force, $all, $break ) && $break ) {
			return 0;
		}
	}
	return 1;
}
/**
 * Removes language files from the language admin folders by filter
 */
function uninstallLanguages( $filter ) {
	$languages = JFolder::folders( JPATH_ADMINISTRATOR.'/language' );
	foreach ( $languages as $lang ) {
		$files = JFolder::files( JPATH_ADMINISTRATOR.'/language/'.$lang, $filter );
		foreach ( $files as $file ) {
			JFile::delete( JPATH_ADMINISTRATOR.'/language/'.$lang.'/'.$file );
		}
	}
}

/**
 * Copies all files from install folder
 */
function copy_from_folder( $folder, $force = 0 )
{
	if ( is_dir ( $folder ) ) {
		// Copy files
		$folders = JFolder::folders( $folder );

		$success = 1;

		foreach ( $folders as $subfolder ) {
			if ( !folder_copy( $folder.'/'.$subfolder, JPATH_SITE.'/'.$subfolder, $force ) ) {
				$success = 0;
			}
		}

		return $success;
	}
}

/**
 * Copy a folder
 */
function folder_copy( $src, $dest, $force = 0 )
{
	$mainframe =& JFactory::getApplication();

	// Initialize variables
	jimport( 'joomla.client.helper' );
	$ftpOptions = JClientHelper::getCredentials( 'ftp' );

	// Eliminate trailing directory separators, if any
	$src = rtrim( str_replace( '\\', '/', $src ), '/' );
	$dest = rtrim( str_replace( '\\', '/', $dest ), '/' );

	if ( !JFolder::exists( $src ) ) {
		return 0;
	}

	$success = 1;

	// Make sure the destination exists
	if ( !JFolder::exists( $dest ) && !folder_create( $dest ) ) {
		$folder = str_replace( JPATH_ROOT, '', $dest );
		$mainframe->enqueueMessage( JText::_( 'NNI_FAILED_TO_CREATE_DIRECTORY' ).': '.$folder, 'error error_folders' );
		$success = 0;
	}

	if ( !( $dh = @opendir( $src ) ) ) {
		return 0;
	}

	$folders = array();
	$files = array();
	while ( ( $file = readdir( $dh ) ) !== false ) {
		if ( $file != '.' && $file != '..' ) {
			$file_src = $src.'/'.$file;
			switch ( filetype( $file_src ) ) {
				case 'dir':
					$folders[] = $file;
					break;
				case 'file':
					$files[] = $file;
					break;
			}
		}
	}
	sort( $folders );
	sort( $files );

	$curr_folder = array_pop( explode( '/', $src ) );
	// Walk through the directory recursing into folders
	foreach ( $folders as $folder ) {
		$folder_src = $src.'/'.$folder;
		$folder_dest = $dest.'/'.$folder;
		if ( !( $curr_folder == 'language' && !JFolder::exists( $folder_dest ) ) ) {
			if ( !folder_copy( $folder_src, $folder_dest, $force ) ) {
				$success = 0;
			}
		}
	}

	if ( $ftpOptions['enabled'] == 1 ) {
		// Connect the FTP client
		jimport( 'joomla.client.ftp' );
		$ftp =& JFTP::getInstance(
			$ftpOptions['host'], $ftpOptions['port'], null,
			$ftpOptions['user'], $ftpOptions['pass']
		);

		// Walk through the directory copying files
		foreach ( $files as $file ) {
			$file_src = $src.'/'.$file;
			$file_dest = $dest.'/'.$file;
			// Translate path for the FTP account
			$file_dest = JPath::clean( str_replace( JPATH_ROOT, $ftpOptions['root'], $file_dest ), '/' );
			if ( $force || !JFile::exists( $file_dest ) ) {
				if ( !$ftp->store( $file_src, $file_dest ) ) {
					$file_path = str_replace( $ftpOptions['root'], '', $file_dest );
					$mainframe->enqueueMessage( JText::_( 'NNI_ERROR_SAVING_FILE' ).': '.$file_path, 'error error_files' );
					$success = 0;
				}
			}
		}
	} else {
		foreach ( $files as $file ) {
			$file_src = $src.'/'.$file;
			$file_dest = $dest.'/'.$file;
			if ( $force || !JFile::exists( $file_dest ) ) {
				if ( !@copy( $file_src, $file_dest ) ) {
					$file_path = str_replace( JPATH_ROOT, '', $file_dest );
					$mainframe->enqueueMessage( JText::_( 'NNI_ERROR_SAVING_FILE' ).': '.$file_path, 'error error_files' );
					$success = 0;
				}
			}
		}
	}

	return $success;
}

/**
 * Create a folder
 */
function folder_create( $path = '', $mode = 0755 )
{
	// Initialize variables
	jimport( 'joomla.client.helper' );
	$ftpOptions = JClientHelper::getCredentials( 'ftp' );

	// Check to make sure the path valid and clean
	$path = JPath::clean( $path );

	// Check if dir already exists
	if ( JFolder::exists( $path ) ) {
		return true;
	}

	// Check for safe mode
	if ( $ftpOptions['enabled'] == 1 ) {
		// Connect the FTP client
		jimport( 'joomla.client.ftp' );
		$ftp =& JFTP::getInstance(
			$ftpOptions['host'], $ftpOptions['port'], null,
			$ftpOptions['user'], $ftpOptions['pass']
		);

		// Translate path to FTP path
		$path = JPath::clean( str_replace( JPATH_ROOT, $ftpOptions['root'], $path ), '/' );
		$ret = $ftp->mkdir( $path );
		$ftp->chmod( $path, $mode );
	} else {
		// We need to get and explode the open_basedir paths
		$obd = ini_get( 'open_basedir' );

		// If open_basedir is set we need to get the open_basedir that the path is in
		if ( $obd != null )
		{
			if ( JPATH_ISWIN ) {
				$obdSeparator = ";";
			} else {
				$obdSeparator = ":";
			}
			// Create the array of open_basedir paths
			$obdArray = explode( $obdSeparator, $obd );
			$inBaseDir = false;
			// Iterate through open_basedir paths looking for a match
			foreach ( $obdArray as $test ) {
				$test = JPath::clean( $test );
				if ( strpos( $path, $test ) === 0 ) {
					$inBaseDir = true;
					break;
				}
			}
			if ( $inBaseDir == false ) {
				// Return false for JFolder::create because the path to be created is not in open_basedir
				JError::raiseWarning(
					'SOME_ERROR_CODE',
					'JFolder::create: '.JText::_( 'NNI_PATH_NOT_IN_OPEN_BASEDIR_PATHS' )
				);
				return false;
			}
		}

		// First set umask
		$origmask = @umask(0);

		// Create the path
		if ( !$ret = @mkdir( $path, $mode ) ) {
			@umask( $origmask );
			return false;
		}

		// Reset umask
		@umask( $origmask );
	}

	return $ret;
}