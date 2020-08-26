<?php

/*
Plugin Name: Advanced Custom Fields: Image Hotspots Field
Plugin URI: PLUGIN_URL
Description: Adds a field to capture coordinates relative to specified images
Version: 0.1
Author: Andrew Rockwell
Author URI: http://www.rockwellgrowth.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_plugin_image_mapping') ) :

class acf_plugin_image_mapping {
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// vars
		$this->settings = array(
			'version'	=> '1.0.0',
			'url'		=> plugin_dir_url( __FILE__ ),
			'path'		=> plugin_dir_path( __FILE__ )
		);
		
		
		// set text domain
		// https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
		load_plugin_textdomain( 'acf-image_mapping', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' ); 
		
		
		// include field
		add_action('acf/include_field_types', 	array($this, 'include_field_types')); // v5
		add_action('acf/register_fields', 		array($this, 'include_field_types')); // v4
		
	}
	
	
	/*
	*  include_field_types
	*
	*  This function will include the field type class
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	$version (int) major ACF version. Defaults to false
	*  @return	n/a
	*/
	
	function include_field_types( $version = false ) {
		
		// support empty $version
		if( !$version ) $version = 4;

		if ( 5 != $version ) {
			add_action( 'admin_notices', array( $this, 'not_compatible' ) );
			return;
		}
		
		// include
		include_once('fields/acf-image_hotspots-v' . $version . '.php');
		
	}

	function not_compatible() {
		?>
			<div class="notice notice-error is-dismissible">
			<p><?php _e( 'ACF: Image Hotspots Field is only compatible for ACF v5+!', 'sample-text-domain' ); ?></p>
			</div>
		<?php
	}

}


// initialize
new acf_plugin_image_mapping();


// class_exists check
endif;
	
?>