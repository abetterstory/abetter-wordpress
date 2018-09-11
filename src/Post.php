<?php

namespace ABetter\Wordpress;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

	public static $post;
	public static $posttypes;

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

	public static function getPostTypes() {
		if (isset(self::$posttypes)) return self::$posttypes;
		self::$posttypes = array_merge(['post','page'],array_keys(get_post_types(['public'=>1,'_builtin'=>0],'names')));
		return self::$posttypes;
	}

	// ---

	public static function prepared() {
		if (empty(self::$post)) return NULL;
		self::$post->prepared = TRUE;
		return self::$post;
	}

}
