<?php

namespace ABetter\Wordpress;

class Component extends \ABetter\Toolkit\Component {

	// --- Public

	public $post;

	// --- Init

	public function init() {
		$this->post = Post::$post;
		$this->item = Posts::buildPost($this->post);
	}

}
