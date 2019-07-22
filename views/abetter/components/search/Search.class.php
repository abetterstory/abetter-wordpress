<?php

namespace App\View\Components;

use \ABetter\Wordpress\Posts;
use \ABetter\Toolkit\Component as BaseComponent;

class SearchComponent extends BaseComponent {

	// --- Variables

	// --- Build

	public function build() {

		$this->query = $_GET['s'] ?? '';

		$this->limit = 12;
		$this->args = ['s' => $this->query, 'post_type' => 'any', 'post_status' => 'publish', 'posts_per_page' => $this->limit];
		$this->result = new Posts($this->args);
		$this->posts = $this->result->posts;

		// ---

		foreach ($this->posts AS &$post) {
			$post->ID = $post->ID ?? 0;
			$post->type = $post->post_type ?? 'post';
			$post->label = ucfirst($post->type);
			$post->headline = (!empty($post->post_title)) ? $post->post_title : _lipsum('short');
			$post->excerpt = _excerpt((!empty($post->post_content)) ? $post->post_content : _lipsum(), 150);
			$post->dateline = ($f = _wp_date('l d F, Y',$post->ID)) ?: date('l d F, Y');
			$post->url = _wp_url($post);
			$post->link = "Read more";
		} unset($post);

	}

}
