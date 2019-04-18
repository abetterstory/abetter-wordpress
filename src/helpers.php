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

	function _wp_view($is=NULL) {
		if ($is) return \ABetter\Wordpress\Controller::isView($is);
		return \ABetter\Wordpress\Controller::getView();
	}

}

if (!function_exists('_wp_post')) {

	function _wp_page($id,$lang=NULL) {
		if (!$id) return \ABetter\Wordpress\Post::$post ?? NULL;
		return \ABetter\Wordpress\Post::getPage($id,$lang);
	}

	function _wp_post($id=NULL,$lang=NULL) {
		if (!$id) return \ABetter\Wordpress\Post::$post ?? NULL;
		return \ABetter\Wordpress\Post::getPage($id,$lang);
	}

}

if (!function_exists('_wp_content')) {

	function _wp_content($post=NULL,$lang=NULL,$return=NULL) {
		if (empty($post->ID)) $post = \ABetter\Wordpress\Post::$post ?? NULL;
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

	function _wp_render_content($post=NULL,$lang=NULL,$return=NULL) {
		$content = _wp_content($post,$lang,$return);
		return _render($content);
	}

}

if (!function_exists('_wp_property')) {

	function _wp_property($key,$post=NULL,$lang=NULL,$return=NULL) {
		if (empty($post->ID)) $post = \ABetter\Wordpress\Post::$post ?? NULL;
		$return = (!empty($post->{$key})) ? $post->{$key} : $return; // Current
		if ($lang === FALSE || empty($post->l10n->translations)) return $return; // No WPML
		if ($lang && ($id = $post->l10n->translations[$lang] ?? NULL) && ($req = get_post($id))) {
			$return = (!empty($req->{$key})) ? $req->{$key} : $return; // Lang
		}
		if (!$return && ($id = $post->l10n->translations[$post->l10n->default] ?? NULL) && ($def = get_post($id))) {
			$return = (!empty($def->{$key})) ? $def->{$key} : $return; // Default
		}
		return $return; // Fallback
	}

	function _wp_render_property($key,$post=NULL,$lang=NULL,$return=NULL) {
		$property = _wp_property($key,$post,$lang,$return);
		return _render($property);
	}

	function _wp_id($post=NULL,$lang=NULL,$return=NULL) {
		return _wp_property('ID',$post,$lang,$return);
	}

}

if (!function_exists('_wp_field')) {

	function _wp_field($key,$post=NULL,$lang=NULL,$return=NULL) {
		if (empty($post->ID)) $post = \ABetter\Wordpress\Post::$post ?? NULL;
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

	function _wp_render_field($key,$post=NULL,$lang=NULL,$return=NULL) {
		$field = _wp_field($key,$post,$lang,$return);
		return _render($field);
	}

}

if (!function_exists('_wp_option')) {

	function _wp_option($key) {
		return ($var = $GLOBALS['wpdb']->get_var('SELECT option_value FROM wp_options WHERE option_name = "'.$key.'"')) ? $var : NULL;
	}

}

if (!function_exists('_wp_url')) {

	function _wp_url($post=NULL,$lang=NULL) {
		return _relative(get_permalink(_wp_id($post,$lang)));
	}

}

if (!function_exists('_wp_title')) {

	function _wp_title($post=NULL,$lang=NULL) {
		return _wp_property('post_title',$post,$lang);
	}

}

if (!function_exists('_wp_template')) {

	function _wp_template($post=NULL,$lang=NULL) {
		$id = _wp_id($post,$lang);
		if (get_option('page_on_front') == $id) return 'front';
		return ($t = get_page_template_slug($id)) ? strtok($t,'.') : _wp_property('post_type',$post, $lang);
	}

}

if (!function_exists('_wp_autotitle')) {

	function _wp_autotitle($content,$post=NULL,$lang=NULL) {
		if (preg_match('/<h1/',$content) || preg_match('/^<(h1|h2)/i',trim($content))) return $content;
		return '<h1 class="autotitle">'._wp_title($post,$lang).'</h1>'.$content;
	}

}

if (!function_exists('_wp_fake')) {

	function _wp_fake($post=NULL,$lang=NULL,$return="") {
		if (empty($post->ID)) $post = \ABetter\Wordpress\Post::$post ?? NULL;
		$return .= '<h1>'._wp_title($post,$lang).'</h1>';
		$return .= '<p class="lead">'._lipsum('medium').'</p>';
		$return .= '<p>'._lipsum('long').'</p>';
		return $return;
	}

}
