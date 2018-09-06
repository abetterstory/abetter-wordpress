<?php

namespace App\View\Components;

use \ABetter\Toolkit\Component as BaseComponent;

class Post extends BaseComponent {

	// --- Variables

	// --- Parse

	public function parse() {

		$post = \ABetter\Wordpress\Post::$post;

		// ---

		$this->content = ($f = _render($post->post_content)) ? $f : _lipsum('body');

		if (!preg_match('/(<h1|\:h1)/',$this->content)) $this->content = "<h1>{$post->post_title}</h1>".PHP_EOL.$this->content;

	}

}
