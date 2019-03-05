<?php

/* Really crappy hack of __ translation helper */

if (!function_exists('__')) {

    function __($key, $par = NULL, $locale = NULL) {

		if (isset($par) && is_array($par)) { // Probably not Wordpress

			 // Foundation
			return trans($key, $par);

		} else if (function_exists('translate')){

			// Wordpress
			$par = (string) (!empty($par)) ? 'default' : $par;
			return translate($key,$par);

		} else {

			// Voyager
			$par = (array) (!empty($par)) ? [] : $par;
			return trans($key, $par);

		}
    }

}

/* resources/wordpress/core/wp-includes/l10n.php
if (!function_exists('__')){function __($text, $domain = 'default' ) {
    return translate($text,$domain);}
}
*/

/* vendor/tcg/voyager/src/Helpers/helpersi18n.php
if (!function_exists('___')) {
    function ___($key, array $par = [])
    {
        return trans($key, $par);
    }
}
*/

/* vendor/laravel/framework/src/Illuminate/Foundation/helpers.php
if (! function_exists('___')) {
    function ___($key, $replace = [], $locale = null)
    {
        return app('translator')->getFromJson($key, $replace, $locale);
    }
}
*/

// ---

if (!function_exists('_wp_view')) {

	function _wp_view($test=NULL) {
		if (!$view = \ABetter\Wordpress\Controller::$handle->view) {
			$ss = \ABetter\Wordpress\Controller::$handle->suggestions ?? [];
			foreach ($ss AS $s) $view = (!$view && \View::exists($s)) ? $s : $view;
		}
		return ($test) ? ($test === $view) : $view;
	}

}

if (!function_exists('_wp_post')) {

	function _wp_page($id,$lang=NULL) {
		return \ABetter\Wordpress\Post::getPage($id,$lang);
	}

	function _wp_post($id,$lang=NULL) {
		return \ABetter\Wordpress\Post::getPage($id,$lang);
	}

}

if (!function_exists('_wp_content')) {

	function _wp_content($post,$lang=NULL,$return=NULL) {
		$return = $post->post_content ?? ""; // Current
		if ($lang === FALSE || empty($post->l10n->translations)) return $return; // No WPML
		if ($lang && ($id = $post->l10n->translations[$lang] ?? NULL) && ($req = get_post($id))) {
			$return = ($f = $req->post_content ?? "") ? $f : $return; // Lang
		}
		if (!$return && ($id = $post->l10n->translations[$post->l10n->default] ?? NULL) && ($def = get_post($id))) {
			$return = ($f = $def->post_content ?? "") ? $f : $return; // Default
		}
		return $return; // Fallback
	}

}

if (!function_exists('_wp_field')) {

	function _wp_field($key,$post,$lang=NULL,$return=NULL) {
		$return = ($f = get_field($key,$post)) ? $f : $return; // Current
		if ($lang === FALSE || empty($post->l10n->translations)) return $return; // No WPML
		if ($lang && ($id = $post->l10n->translations[$lang] ?? NULL) && ($req = get_post($id))) {
			$return = ($f = get_field($key,$req)) ? $f : $return; // Lang
		}
		if (!$return && ($id = $post->l10n->translations[$post->l10n->default] ?? NULL) && ($def = get_post($id))) {
			$return = ($f = get_field($key,$def)) ? $f : $return; // Default
		}
		return $return; // Fallback
	}

}

if (!function_exists('_wp_option')) {

	function _wp_option($key) {
		return ($var = $GLOBALS['wpdb']->get_var('SELECT option_value FROM wp_options WHERE option_name = "'.$key.'"')) ? $var : NULL;
	}

}

if (!function_exists('_wp_url')) {

	function _wp_url($post) {
		return _relative(get_permalink($post));
	}

}

if (!function_exists('_wp_title')) {

	function _wp_title($post) {
		return $post->post_title ?? "";
	}

}
