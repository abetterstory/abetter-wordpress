<?php
/* 
Plugin Name: Advanced Custom Fields Multilingual
Description: This 'glue' plugin makes it easier to translate with WPML content provided in fields created with Advanced Custom Fields
Author: OnTheGoSystems
Plugin URI: https://wpml.org/
Author URI: http://www.onthegosystems.com/
Version: 0.9
 */

$autoloader_dir = __DIR__ . '/vendor';
if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
	$autoloader = $autoloader_dir . '/autoload.php';
} else {
	$autoloader = $autoloader_dir . '/autoload_52.php';
}
require_once $autoloader;

$WPML_ACF = new WPML_ACF();
$WPML_ACF = $WPML_ACF->init_worker();

add_action('admin_enqueue_scripts', 'acfml_enqueue_scripts');

function acfml_enqueue_scripts() {
	if (is_admin()) {
		wp_enqueue_script('acfml_js', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'));
	}	
}


