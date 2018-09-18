<?php

namespace ABetter\Wordpress;

class Component extends ABetter\Toolkit\Component {

	// --- Private

	public $post;

	// --- Init

	public function init() {
		$this->post = ABetter\Wordpress\Post::$post;
	}

}
