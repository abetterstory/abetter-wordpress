<?php
/*
Wordpress config
*/

// Add require to wp-config.php
// require_once(dirname(__FILE__).'/wp-content/themes/abetter/config.php');

define('ROOTPATH', realpath(dirname(__FILE__).'/../../../../../'));

require_once(ROOTPATH.'/vendor/vlucas/phpdotenv/src/Dotenv.php');
require_once(ROOTPATH.'/vendor/vlucas/phpdotenv/src/Loader.php');

$dotenv = new Dotenv\Dotenv(ROOTPATH);
$dotenv->load();

// ---

define('WP_DEBUG', 			getenv('WP_DEBUG'));

define('DB_NAME', 			getenv('WP_DB_NAME'));
define('DB_USER', 			getenv('WP_DB_USER'));
define('DB_PASSWORD', 		getenv('WP_DB_PASSWORD'));
define('DB_HOST', 			getenv('WP_DB_HOST'));
define('DB_CHARSET', 		getenv('WP_DB_CHARSET'));
define('DB_COLLATE', 		getenv('WP_DB_COLLATE'));

define('AUTH_KEY',         	getenv('WP_AUTH_KEY'));
define('SECURE_AUTH_KEY',  	getenv('WP_SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY',    	getenv('WP_LOGGED_IN_KEY'));
define('NONCE_KEY',        	getenv('WP_NONCE_KEY'));
define('AUTH_SALT',        	getenv('WP_AUTH_SALT'));
define('SECURE_AUTH_SALT',	getenv('WP_SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT',   	getenv('WP_LOGGED_IN_SALT'));
define('NONCE_SALT',       	getenv('WP_NONCE_SALT'));

$table_prefix = 			getenv('WP_DB_PREFIX');
