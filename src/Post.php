<?php

namespace ABetter\Wordpress;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

	public static $post;

	// ---

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
	}

	// ---

	public function getFront() {
		self::$post = get_post(get_option('page_on_front'));
		return self::prepared();
	}

	public function getPost($slug=NULL) {
		self::$post = ($p = get_page_by_path($slug,OBJECT,['post','page'])) ? $p : NULL;
		return self::prepared();
	}

	// ---

	public function prepared() {
		if (empty(self::$post)) return NULL;
		self::$post->prepared = TRUE;
		return self::$post;
	}

}
