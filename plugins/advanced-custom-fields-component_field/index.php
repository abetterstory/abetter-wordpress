<?php
/*
    Plugin Name: Advanced Custom Fields: Component Field
    Plugin URI: https://acf-component-field.gummi.io/
    Description: Acvanced Custom Fields add on. Make an entire acf field group reuseable, as a component field.
    Version: 2.0.2
    Author: Gummi.IO
    Author URI: https://gummi.io
    License: GPLv2 or later
    License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('ABSPATH') or die('No script kiddies please!');

require_once dirname(__FILE__) . '/vendor/autoload.php';

global $acfComponentField;
$acfComponentField = new \GummiIO\AcfComponentField\Core(__FILE__, '2.0.2');
