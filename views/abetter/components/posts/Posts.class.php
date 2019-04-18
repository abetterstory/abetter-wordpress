<?php

namespace App\View\Components;

use \ABetter\Wordpress\Posts;
use \ABetter\Toolkit\Component as BaseComponent;

class PostsComponent extends BaseComponent {

	// --- Variables

	// --- Build

	public function build() {

		$this->news = _wp_page('news');
		$this->template = _wp_template();
		$this->widget = ($this->template != 'posts') ? TRUE : FALSE;

		$this->limit = ($this->widget) ? 5 : 12;
		$this->args = ['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => $this->limit];
		$this->result = new Posts($this->args);
		$this->posts = $this->result->posts;

		// ---

		while(count($this->posts) < $this->limit) {
			$this->posts = array_merge($this->posts, [new \StdClass()]);
		}

		foreach ($this->posts AS &$post) {
			$post->ID = $post->ID ?? 0;
			$post->type = $post->post_type ?? 'post';
			$post->label = "News";
			$post->headline = (!empty($post->post_title)) ? $post->post_title : _lipsum('short');
			$post->excerpt = _excerpt((!empty($post->post_content)) ? $post->post_content : _lipsum(), 150);
			$post->dateline = ($f = get_the_date('l d F, Y',$post->ID)) ?: date('l d F, Y');
			$post->url = _wp_url($post);
			$post->image = _pixsum('photo');
			$post->link = "Read more";
		} unset($post);

		// ---

		if ($this->widget) {
			$this->more = new \StdClass();
			$this->more->type = 'more';
			$this->more->headline = _lipsum('short');
			$this->more->excerpt = _lipsum('medium');
			$this->more->more = _wp_url($this->news);
			$this->more->link = "More news";
			$this->posts[] = $this->more;
		}

		// ---

		$this->intro = _render($this->slot ?? "");

		// ---

		if (!$this->intro && $this->template == 'front') {
			$this->intro = "<center><h1>"._lipsum('headline')."</h1><p>"._lipsum('normal')."</p></center>";
		}

	}

}
