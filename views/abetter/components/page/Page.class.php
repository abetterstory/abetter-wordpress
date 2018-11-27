<?php

namespace App\View\Components;

use \ABetter\Toolkit\Component as BaseComponent;

class Page extends BaseComponent {

	// --- Variables

	// --- Build

	public function build() {

		$this->post = \ABetter\Wordpress\Post::$post;

		// ---

		$this->content = ($f = _render(_wp_content($this->post))) ? $f : _lipsum('body');

		if (!preg_match('/(<h1|\:h1)/',$this->content)) $this->content = "<h1>{$this->post->post_title}</h1>".PHP_EOL.$this->content;

	}

}
