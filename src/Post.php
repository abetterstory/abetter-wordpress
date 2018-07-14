<?php

namespace ABetter\Wordpress;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

	public static $post;
	public static $posttypes;

	// ---

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
	}

	// ---

	public static function getFront() {
		self::$post = get_post(get_option('page_on_front'));
		return self::prepared();
	}

	public static function getPost($slug=NULL) {
		// WP slug don't include full path for subpages
		$try = trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),'/');
		if (preg_match('/^\d{4}\/\d{2}\/\d{2}\//',$try)) $try = $slug; // Safe for archive slugs
		self::$post = ($p = get_page_by_path($try,OBJECT,self::getPostTypes())) ? $p : NULL;
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
