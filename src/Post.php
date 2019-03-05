<?php

namespace ABetter\Wordpress;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

	public static $post;
	public static $error;

	public static $posttypes;
	public static $front;
	public static $news;
	public static $privacy;

	public static $languages;
	public static $language;
	public static $language_default;
	public static $language_request;

	public static $translation;
	public static $translations;

	public static $cache;

	// --- Constructor

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
	}

	// ---

	public static function getFront() {
		self::$post = get_post(get_option('page_on_front')); // WPML filter this to current language
		self::$post = self::getPostPreview();
		self::$post = self::getPostL10n();
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
		// Fix WPML problem with identical slugs on translations
		if (function_exists('icl_object_id')) {
			if (($id = apply_filters('wpml_object_id', self::$post->ID)) && $id !== self::$post->ID) {
				self::$post = get_post($id);
			}
		}
		self::$post = self::getPostPreview();
		self::$post = self::getPostError();
		self::$post = self::getPostL10n();
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

	public static function getPostL10n() {
		if (isset(self::$post->l10n)) return self::$post;
		self::$post->l10n = new \StdClass();
		self::$post->l10n->default = self::getDefaultLanguage();
		self::$post->l10n->request = self::getRequestLanguage();
		self::$post->l10n->language = self::getLanguage(self::$post);
		self::$post->l10n->translations = self::getTranslations(self::$post);
		self::$post->language = self::$post->l10n->language;
		return self::$post;
	}

	// ---

	public static function getL10n($post) {
		$l10n = new \StdClass();
		$l10n->default = self::getDefaultLanguage();
		$l10n->request = self::getRequestLanguage();
		$l10n->language = self::getLanguage($post);
		$l10n->translations = self::getTranslations($post);
		return $l10n;
	}

	public static function getLanguage($post) {
		$language = self::getDefaultLanguage();
 		if (function_exists('icl_object_id')) {
 			$language = ($lc = $GLOBALS['wpdb']->get_var("SELECT language_code FROM wp_icl_translations WHERE (element_id = \"{$post->ID}\" AND element_type = \"post_{$post->post_type}\")")) ? $lc : $language;
 		}
		return $language;
	}

	public static function getTranslations($post) {
		$translations = [];
 		if (function_exists('icl_object_id')) {
			$trid = $GLOBALS['wpdb']->get_var("SELECT trid FROM wp_icl_translations WHERE (element_id = \"{$post->ID}\" AND element_type = \"post_{$post->post_type}\")");
 			$results = ($tr = $GLOBALS['wpdb']->get_results("SELECT language_code,element_id FROM wp_icl_translations WHERE (trid = \"{$trid}\" AND element_type = \"post_{$post->post_type}\")",ARRAY_N)) ? $tr : $translations;
			foreach ($results AS $row) $translations[(string)$row[0]] = (integer)$row[1];
			//global $sitepress;
			//$trid = $sitepress->get_element_trid($post->ID,'post_'.$post->post_type);
			//$results = $sitepress->get_element_translations($trid,'post_'.$post->post_type);
			//foreach ($results AS $row) $translations[$row->language_code] = (integer) $row->element_id;
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

	public static function getPage($id,$language=NULL) {
		$post = NULL;
		if (is_object($id)) {
			$post = $id;
		} else if (is_numeric($id)) {
			$post = get_post($id);
		} else if (is_string($id)) {
			$post = get_page_by_path($id);
		}
		if (!$post) return NULL;
		$post->l10n = $post->l10n ?? self::getL10n($post);
		$post = self::getTranslated($post);
		return $post;
	}

	public static function getTranslated($post,$language=NULL) {
		if (!function_exists('icl_object_id') || empty($post->l10n) || self::getDefaultLanguage() == self::getRequestLanguage()) return $post;
		if ($id = $post->l10n->translations[self::getRequestLanguage()]) {
			$post = get_post($id);
			$post->l10n = $post->l10n ?? self::getL10n($post);
		}
		return $post;
	}

	// ---

	public static function isFront($post,$true='front',$false='') {
		return (in_array($post->ID,self::getFrontPages())) ? $true : $false;
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
		return self::$language_default;
	}

	public static function getRequestLanguage() {
		if (!empty(self::$language_request)) return self::$language_request;
		if (function_exists('icl_object_id')) {
			self::$language_request = ICL_LANGUAGE_CODE;
		} else {
			self::$language_request = self::getDefaultLanguage();
		}
		return self::$language_request;
	}

	public static function getPostTypes() {
		if (isset(self::$posttypes)) return self::$posttypes;
		self::$posttypes = array_merge(['page','post'],array_keys(get_post_types(['public'=>1,'_builtin'=>0],'names')));
		return self::$posttypes;
	}

	public static function getFrontPages() {
		if (isset(self::$front)) return self::$front;
		self::$front = ($front = _wp_option('page_on_front')) ? (array) $front : [];
		if (function_exists('icl_object_id')) {
			self::$front = ($result = $GLOBALS['wpdb']->get_results('SELECT element_id FROM wp_icl_translations WHERE trid = "'.$front.'"', ARRAY_N)) ? $result : self::$front;
			foreach (self::$front AS &$row) $row = reset($row);
		}
		return self::$front;
	}

	public static function getNewsPages() {
		if (isset(self::$news)) return self::$news;
		self::$news = ($news = _wp_option('page_for_posts')) ? (array) $news : [];
		if (function_exists('icl_object_id')) {
			self::$news = ($result = $GLOBALS['wpdb']->get_results('SELECT element_id FROM wp_icl_translations WHERE trid = "'.$news.'"', ARRAY_N)) ? $result : self::$news;
			foreach (self::$news AS &$row) $row = reset($row);
		}
		return self::$news;
	}

	public static function getPrivacyPages() {
		if (isset(self::$privacy)) return self::$privacy;
		self::$privacy = ($privacy = _wp_option('wp_page_for_privacy_policy')) ? (array) $privacy : [];
		if (function_exists('icl_object_id')) {
			self::$privacy = ($result = $GLOBALS['wpdb']->get_results('SELECT element_id FROM wp_icl_translations WHERE trid = "'.$privacy.'"', ARRAY_N)) ? $result : self::$privacy;
			foreach (self::$privacys AS &$row) $row = reset($row);
		}
		return self::$privacy;
	}

	// ---

	public static function prepared() {
		if (empty(self::$post)) return NULL;
		self::$post->prepared = TRUE;
		return self::$post;
	}

}
