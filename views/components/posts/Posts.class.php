<?php

namespace App\View\Components;

use \ABetter\Toolkit\Component as BaseComponent;

class Posts extends BaseComponent {

	// --- Variables

	// --- Parse

	public function parse() {

		$this->post = \ABetter\Wordpress\Post::$post;

		$this->content = ($f = _render($this->post->post_content)) ? $f : _lipsum('lead:p');

		if (!preg_match('/(<h1|\:h1)/',$this->content)) $this->content = "<h1>{$this->post->post_title}</h1>".PHP_EOL.$this->content;

		// ---

		$this->posts = new \ABetter\Wordpress\Posts(['numberposts' => 5, 'fake' => TRUE]);
		$this->items = $this->posts->items;

	}

}
