<?php
/**
 * Plugin Name: WP Focal Point
 * Version: 1.2.0
 * Plugin URI: https://github.com/BenjaminMedia/wp-focal-point
 * Description: This plugin allows setting a focal point on an image
 * Author: Bonnier
 * License: GPL v3
 */

require_once('src/FocalPoint.php');

// ---

if (!defined('ABSPATH')) {
    exit;
}

function register_focal_point_plugin()
{
    return \Bonnier\WP\FocalPoint\FocalPoint::instance();
}

add_action('plugins_loaded', 'register_focal_point_plugin');
