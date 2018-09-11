<?php

namespace ABetter\Wordpress;

class Index {

	public $scope;
	public $items;

	public static $posts;
	public static $index;
	public static $cleanup;

	// --- Constructor

	public function __construct($defined_vars = []) {
		$this->scope = (object) $defined_vars;
		$this->items = self::build();
	}

	// ---

	public static function build($props=NULL) {

		Controller::loadWp();

		self::$posts = get_posts(['post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC']);
		self::$index = [];
		self::$cleanup = [];

		// Pass 1 : Data
		foreach (self::$posts AS $post) {
			$item = new \StdClass();
			$item->id = (int) $post->ID;
			$item->label = (string) $post->post_title;
			$item->status = (string) $post->post_status;
			$item->url = (string) _relative(get_permalink($post));
			if ($item->status != 'publish') $item->url = preg_replace('/\?(page_id|p)=([0-9]+)/',"$1/$2/",$item->url);
			$item->order = (int) $post->menu_order;
			$item->parent = (int) $post->post_parent;
			$item->edit = "/wp/wp-admin/post.php?action=edit&post={$item->id}";
			$item->selector = "wp-{$post->post_type}-{$item->id}";
			$item->preview = _relative(get_permalink($post));
			$item->current = (string) _is_current($item->url,'current');
			$item->front = (string) _is_front($item->url,'front');
			$item->items = array();
			self::$index[$item->id] = $item;
		}

		// Pass 2 : Hierarchy
		foreach (self::$index AS $item) {
			if ($item->parent && isset(self::$index[$item->parent])) {
				self::$index[$item->parent]->items[$item->id] = $item;
				self::$cleanup[] = $item->id;
			}
		}

		// Pass 3 : Cleanup
		foreach (self::$cleanup AS $id) {
			unset(self::$index[(string)$id]);
		}

		return self::$index;

	}

}
