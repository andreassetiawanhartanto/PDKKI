<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'pdkki_wp571');

/** MySQL database username */
define('DB_USER', 'pdkki_wp571');

/** MySQL database password */
define('DB_PASSWORD', 'cPw5gs8Sk6');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'p7hcvfdpyq0nknvnggpwwh3codgakqy6sr6dnsb7gdasawik0n6fqxyledq6dsi0');
define('SECURE_AUTH_KEY',  'fiolsoimol0lobniwwnv2osweihfsidmi5pyxqzg2uddbpr0j1ihoicvznehxcyh');
define('LOGGED_IN_KEY',    'tbqtgbu8mrv3q7t72oofbyi2zcyykaal3ygxjuv3lonu46sxyhkuolal4ngq4syf');
define('NONCE_KEY',        'vhzxjohykcvlh22s1mcglyumfyuy5iakhgukquihjjkp4spqf09unpvzilnixof5');
define('AUTH_SALT',        '7nll2hnrqw4qc4alt8comh4xsnkltqatdbwmhmjzdmj5yrixgwrobl09mwooijmp');
define('SECURE_AUTH_SALT', 'yspzjttxcanhutrgfzwa6brojvpu3uw28bnypzlv3irzwezcjmylmug7kiu9drtd');
define('LOGGED_IN_SALT',   '3lbytmmlp2rrl9sfapkmm9ilxkve7ajzufjd0dijrchzntfpnqhj2suldt3m5kuk');
define('NONCE_SALT',       'wfwizapnoexczc4yke2lossoh0o7uab1f1hofkj8gdi1y7oyfjr8rwah3r9leamz');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define ('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
