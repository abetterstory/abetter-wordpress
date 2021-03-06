<?php

define('ROOTPATH', realpath(dirname(__FILE__).'/../../../../'));

require_once(ROOTPATH.'/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::create(ROOTPATH);
$dotenv->load();

define('WP_HOME', ($e = getenv('WP_HOME')) ? $e : rtrim(getenv('APP_URL'),'/').'/wp/');
define('WP_SITEURL', ($e = getenv('WP_HOME')) ? $e : rtrim(getenv('APP_URL'),'/').'/wp/');
define('FS_METHOD', 'direct');
define('COOKIEPATH', '/');
define('DISABLE_WP_CRON', TRUE);

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', ($e = getenv('WP_DB_NAME')) ? $e : getenv('DB_DATABASE'));

/** MySQL database username */
define('DB_USER', ($e = getenv('WP_DB_USER')) ? $e : getenv('DB_USERNAME'));

/** MySQL database password */
define('DB_PASSWORD', ($e = getenv('WP_DB_PASSWORD')) ? $e : getenv('DB_PASSWORD'));

/** MySQL hostname */
define('DB_HOST', ($e = getenv('WP_DB_HOST')) ? $e : 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', ($e = getenv('WP_DB_CHARSET')) ? $e : 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', getenv('WP_DB_COLLATE'));

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         getenv('WP_AUTH_KEY'));
define('SECURE_AUTH_KEY',  getenv('WP_SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY',    getenv('WP_LOGGED_IN_KEY'));
define('NONCE_KEY',        getenv('WP_NONCE_KEY'));
define('AUTH_SALT',        getenv('WP_AUTH_SALT'));
define('SECURE_AUTH_SALT', getenv('WP_SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT',   getenv('WP_LOGGED_IN_SALT'));
define('NONCE_SALT',       getenv('WP_NONCE_SALT'));

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = ($e = getenv('WP_DB_PREFIX')) ? $e : 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', (($e = getenv('WP_DEBUG') ?: getenv('APP_DEBUG')) && ($e == 'true')) ? TRUE : FALSE);

/* Test database connection */
if (!DB_NAME || !($mysqli = @mysqli_init()) || !(@$mysqli->real_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME))) {
	if (WP_DEBUG) { @header('Abort: Unable to establish a database connection'); abort(500); } else { abort(404); }
}

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
