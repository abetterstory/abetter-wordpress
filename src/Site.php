<?php

namespace ABetter\Wordpress;

use Illuminate\Database\Eloquent\Model;

class Site extends Model {

	public static $site;

	// --- Constructor

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
	}

	// ---

	public static function getSite() {
		self::$site = new \StdClass();
		//self::$site->ga = "XXX-XXXXX";
		return self::prepared();
	}

	// ---

	public static function prepared() {
		if (empty(self::$site)) return NULL;
		self::$site->prepared = TRUE;
		return self::$site;
	}

}
