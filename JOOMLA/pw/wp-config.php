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
define('DB_NAME', 'pdkki_wp618');

/** MySQL database username */
define('DB_USER', 'pdkki_wp618');

/** MySQL database password */
define('DB_PASSWORD', 'w7jx5lS3Pk');

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
define('AUTH_KEY',         'c47fmngxrifa4oao2oithmqgve536rqagw0lbazfgkjfvvvyc0fwt9hrhp1pidul');
define('SECURE_AUTH_KEY',  '5kclmqs0lyz57q8f01h0l8sihooptdgu6gepkkrjr0qgpby0dzohwij77dh99a9z');
define('LOGGED_IN_KEY',    'ayghzz1guomu0zdg0tyjuvb8e7hckssgsn4eqkygpvv2fxqfsxlyebfxkbojiwwt');
define('NONCE_KEY',        'mivxzwopeot5xvm3cnpoazyffwp94ofmbcbvj8uxcy8znxn2tvlfbs0pumqhkqwr');
define('AUTH_SALT',        'kzfp1zbzj3epgfsnhqmnnh0fvu9mlr02ehnvflhlfjbbgyy0u3xbtjjxf6kiekch');
define('SECURE_AUTH_SALT', 'qqpp5jvbrjresn5z17xwilobzl5g6fmyudnr3cn5ph7tmacinqoujrf6reoapx4m');
define('LOGGED_IN_SALT',   'yrqzvid9p1aj5s5lno9rw0ldi6ygyeczvvbgbmysvfxrz4mcpheudua8yhfufx29');
define('NONCE_SALT',       'n2qw6xdwka2g0bxphssqkqafilzujjmy8r2e5dtgak8exprv1opwg4prnrbgtllw');

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
define ('WPLANG', 'en');

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
