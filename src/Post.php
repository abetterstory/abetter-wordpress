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
		return self::prepared();
	}

	public static function getPost($slug=NULL) {
		if (!self::$post = ($p = get_page_by_path($slug,OBJECT,self::getPostTypes())) ? $p : NULL) {
			$slug = trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),'/'); // Try real path for subpages
			self::$post = ($p = get_page_by_path($slug,OBJECT,self::getPostTypes())) ? $p : NULL;
		}
		return self::prepared();
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
