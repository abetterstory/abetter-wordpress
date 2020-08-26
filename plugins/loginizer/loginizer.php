<?php
/**
 * @package loginizer
 * @version 1.5.5
 */
/*
Plugin Name: Loginizer
Plugin URI: https://wordpress.org/extend/plugins/loginizer/
Description: Loginizer is a WordPress plugin which helps you fight against bruteforce attack by blocking login for the IP after it reaches maximum retries allowed. You can blacklist or whitelist IPs for login using Loginizer.
Version: 1.5.5
Text Domain: loginizer
Author: Softaculous
Author URI: https://www.loginizer.com
License: LGPLv2.1
*/

/*
Copyright (C) 2013 Loginizer (email : support@loginizer.com)
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

function loginizer_load_plugin_textdomain(){
    load_plugin_textdomain( 'loginizer', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'loginizer_load_plugin_textdomain' );

$_ltmp_plugins = get_option('active_plugins');

// Is the premium plugin loaded ?
if(in_array('loginizer-security/loginizer-security.php', $_ltmp_plugins)){
	return;
}

// Is the premium plugin active ?
if(defined('LOGINIZER_VERSION')){
	return;
}

$plugin_loginizer = plugin_basename(__FILE__);
define('LOGINIZER_FILE', __FILE__);
define('LOGINIZER_API', 'https://api.loginizer.com/');

include_once(dirname(__FILE__).'/init.php');

