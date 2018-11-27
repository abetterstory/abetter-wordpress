<?php

namespace App\View\Components;

use \ABetter\Toolkit\Component as BaseComponent;

class Posts extends BaseComponent {

	// --- Variables

	// --- Build

	public function build() {

		$this->post = get_post(get_option('page_for_posts'));

		$this->content = ($f = _render(_wp_content($this->post))) ? $f : _lipsum('lead:p');

		if (!preg_match('/(<h1|\:h1)/',$this->content)) $this->content = "<h1>{$this->post->post_title}</h1>".PHP_EOL.$this->content;

		// ---

		$this->posts = new \ABetter\Wordpress\Posts(['post_type' => 'post', 'posts_per_page' => 5, 'fake' => TRUE]);
		$this->items = $this->posts->items;

	}

}
