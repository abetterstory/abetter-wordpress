<?php

namespace ABetter\Wordpress;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

	public static $post;
	public static $error;
	public static $posttypes;

	public static $languages;
	public static $language;
	public static $language_default;
	public static $language_request;

	// --- Constructor

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
	}

	// ---

	public static function getFront() {
		self::$post = get_post(get_option('page_on_front'));
		self::$post = self::getPostPreview();
		return self::prepared();
	}

	public static function getPost($slug=NULL) {
		$request = basename(preg_replace('/[0-9]+\/[0-9]+\/[0-9]+\//','',$slug)); // Remove archive dates
		$draft = (preg_match('/(page_id|p)\/([0-9]+)/',$slug,$match)) ? $match[2] : NULL;
		if ($draft && get_current_user_id()) {
			self::$post = ($p = get_post($draft)) ? $p : NULL;
		} else {
			if (!self::$post = ($p = get_page_by_path($request,OBJECT,self::getPostTypes())) ? $p : NULL) {
				self::$post = ($p = get_page_by_path($slug,OBJECT,self::getPostTypes())) ? $p : NULL;
			}
		}
		self::$post = self::getPostPreview();
		self::$post = self::getPostError();
		return self::prepared();
	}

	public static function getPostPreview() {
		if (empty($_GET['preview']) || empty(self::$post->ID) || !get_current_user_id()) return self::$post;
		$revisions = wp_get_post_revisions(self::$post->ID);
		$previous = ($revisions) ? reset($revisions) : NULL;
		$preview = (!empty($previous->ID)) ? get_post($previous->ID) : NULL;
		self::$post = ($preview) ? $preview : self::$post;
		return self::$post;
	}


	public static function getPostError() {
		if (isset(self::$post->ID)) return self::$post;
		self::$error = ($p = $GLOBALS['wpdb']->get_results('SELECT * FROM wp_posts WHERE post_name LIKE "404%"')) ? reset($p) : NULL;
		if (empty(self::$error->post_name)) abort(404);
		self::$error->error = 404;
		return self::$error;
	}

	public static function getPostTypes() {
		if (isset(self::$posttypes)) return self::$posttypes;
		self::$posttypes = array_merge(['page','post'],array_keys(get_post_types(['public'=>1,'_builtin'=>0],'names')));
		return self::$posttypes;
	}

	// ---

	public static function getDefaultLanguage() {
		if (!empty(self::$language_default)) return self::$language_default;
		if (function_exists('icl_object_id')) {
			global $sitepress;
			self::$language_default = $sitepress->get_default_language();
		} else {
			self::$language_default = strtolower(strtok(get_bloginfo('language'),'-'));
		}
	}

	public static function getRequestLanguage() {
		if (!empty(self::$language_request)) return self::$language_request;
		if (defined('ICL_LANGUAGE_CODE')) {
			self::$language_request = ICL_LANGUAGE_CODE;
		} else {
			self::$language_request = self::getDefaultLanguage();
		}
		return self::$language_request;
	}

	// ---

	public static function getLanguage($post) {
		$language = self::getDefaultLanguage();
 		if (function_exists('icl_object_id')) {
			$id = (is_object($post) && isset($post->ID)) ? $post->ID : $post;
 			$language = ($lc = $GLOBALS['wpdb']->get_var('SELECT language_code FROM wp_icl_translations WHERE element_id = "'.$id.'"')) ? $lc : $language;
 		}
		return $language;
	}

	public static function getTranslations($post) {
		$translations = [];
 		if (function_exists('icl_object_id')) {
			$id = (is_object($post) && isset($post->ID)) ? $post->ID : $post;
 			$results = ($tr = $GLOBALS['wpdb']->get_results('SELECT language_code,element_id FROM wp_icl_translations WHERE trid = "'.$id.'"',ARRAY_N)) ? $tr : $translations;
			foreach ($results AS $row) $translations[(string)$row[0]] = (integer)$row[1];
 		}
		return $translations;
	}

	public static function getTranslation($post,$language) {
		if (!$translations = self::getTranslations($post)) return NULL;
		foreach ($translations AS $lc => $id) {
			if ($lc == $language) return $id;
		}
		return NULL;
	}

	// ---

	public static function isFront($post,$true='front',$false='') {
		$id = (is_object($post) && isset($post->ID)) ? $post->ID : $post;
		if (($front = get_option('page_on_front')) && $front == $id) return $true;
		return $false;
	}

	// ---

	public static function prepared() {
		if (empty(self::$post)) return NULL;
		self::$post->prepared = TRUE;
		return self::$post;
	}

}
