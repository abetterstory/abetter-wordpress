<?php

namespace ABetter\Wordpress;

class Posts {

	public $args;
	public $query;
	public $posts;
	public $ids;
	public $count;
	public $total;
	public $more;
	public $meta;
	public $items;

	public static $cache;

	// --- Constructor

	public function __construct($defined_vars = []) {
		$this->args = (object) $defined_vars;
		$this->items = $this->build();
	}

	// ---

	public function build($props=NULL) {

		Controller::loadWp();

		$this->args = array_replace([
			'post_type' => 'any',
			'post_status' => 'publish',
			'orderby' => 'date',
			'order' => 'DESC',
			'posts_per_page' => -1,
			'suppress_filters' => TRUE,
			'fake' => FALSE,
		],(array)$this->args);

		if (isset($this->args['numberposts'])) {
			$this->args['posts_per_page'] = (int) $this->args['numberposts'];
		}

		if (isset($this->args['s'])) {
			$this->args['post__in'] = $this->searchItems($this->args['s']);
			unset($this->args['s']);
		}

		$this->query = new \WP_Query($this->args);
		$this->posts = (!empty($this->query->posts)) ? $this->query->posts : [];
		$this->count = count($this->posts);
		$this->total = (int) $this->query->found_posts;
		$this->more = ($this->total > $this->count) ? TRUE : FALSE;
		$this->ids = [];
		$this->items = [];

		foreach ($this->posts AS $post) {
			$this->items[$post->ID] = $this->buildItem($post);
			$this->ids[] = $post->ID;
		}

		$this->meta = $this->getItemsMeta($this->items);

		// ---

		if ($this->args['fake'] && count($this->items) < $this->args['posts_per_page']) {
			while (count($this->items) < $this->args['posts_per_page']) {
				$this->items[] = $this->fakeItem();
			}
		}

		return $this->items;

	}

	// ---

	public function buildItem($post) {
		if (isset(self::$cache['post'][$post->ID])) return self::$cache['post'][$post->ID];
		$item = self::buildPost($post);
		self::$cache['post'][$post->ID] = $item;
		return $item;
	}

	// ---

	public function searchItems($s,$post__in=[]) {
		$keys = apply_filters('search_meta_keys',[]) ?? [];
		$query = "SELECT DISTINCT post_id FROM wp_postmeta WHERE (meta_value LIKE '%{$s}%')";
		$conditions = []; foreach ($keys AS $key) $conditions[] = "(meta_key LIKE '%{$key}' AND meta_value LIKE '%{$s}%')";
		if ($conditions) $query = "SELECT DISTINCT post_id FROM wp_postmeta WHERE (".implode(' OR ',$conditions).")";
		$search_meta = $GLOBALS['wpdb']->get_col($query);
		$search_posts = $GLOBALS['wpdb']->get_col("SELECT DISTINCT ID FROM wp_posts WHERE (post_title LIKE '%{$s}%' OR post_content LIKE '%{$s}%')");
		$post__in = array_merge($post__in, $search_meta, $search_posts);
		return ($post__in) ? $post__in : [0];
	}

	// ---

	public function fakeItem() {
		$item = new \StdClass();
		$item->type = "fake";
		$item->url = "#fake";
		$item->label = _lipsum('label');
		$item->title = _lipsum('label');
		$item->headline = _lipsum('headline');
		$item->lead = _lipsum('lead');
		$item->excerpt = _excerpt(_lipsum('normal'),400);
		$item->image = _pixsum('photo');
		$item->link = _lipsum('label');
		return $item;
	}

	// ---

	public static function buildPost($post) {
		if (!isset($post->ID) || !_wp_loaded()) return NULL;
		$item = new \StdClass();
		$item->post = $post;
		$item->id = (int) $post->ID;
		$item->type = (string) $post->post_type;
		$item->type_label = (string) ($d = _dictionary($post->post_type.'_label',NULL,'')) ? $d : ucfirst($item->type);
		$item->author = (array) self::_wp_author($post);
		$item->category = (array) self::_wp_categories($post);
		$item->tag = (array) self::_wp_tags($post);
		$item->status = (string) $post->post_status;
		$item->slug = (Post::isFront($post,TRUE)) ? '/' : urldecode(get_page_uri($post));
		$item->url = (string) _relative(get_permalink($post));
		$item->order = (int) $post->menu_order;
		$item->parent = (int) $post->post_parent;
		$item->current = (string) _is_current($item->url,'current');
		$item->front = Post::isFront($post,'front');
		// ---
		$item->l10n = Post::getL10n($post);
		$item->language = $item->l10n->language;
		// ---
		$item->timestamp = (int) get_the_date('U',$post);
		$item->date = (string) get_the_date('Y-m-d',$post);
		$item->time = (string) get_the_date('H:i',$post);
		$item->datetime = (string) get_the_date('Y-m-d H:i',$post);
		$item->datelong = (string) get_the_date('F j, Y',$post);
		// ---
		$item->title = (string) htmlspecialchars_decode($post->post_title);
		$item->label = (string) _wp_field('cover_label',$post) ?: _wp_field('header_label',$post);
		$item->headline = (string) _wp_field('cover_headline',$post) ?: _wp_field('header_headline',$post);
		$item->lead = (string) _wp_field('cover_lead',$post) ?: _wp_field('header_lead',$post);
		$item->excerpt = (string) trim(strip_tags($item->lead));
		$item->content = (string) _wp_content($post);
		if (!$item->headline && preg_match('/<h1[^>]*>([^<]+)<\/h1>(.*)/i',$item->content,$match)) {
			$item->headline = trim(strip_tags($match[1]));
			if ($match[2]) $item->content = trim($match[2]);
		}
		if (!$item->lead && preg_match('/<p[^>]*lead[^>]*>([^<]+)<\/p>(.*)/i',$item->content,$match)) {
			$item->lead = trim(strip_tags($match[1]));
			if ($match[2]) $item->content = trim($match[2]);
		}
		if (!$item->headline) $item->headline = $item->title;
		if (!$item->excerpt) $item->excerpt = _excerpt(($item->lead)?$item->lead:$item->content,400);
		// ---

		// ---
		$item->image = (string) _wp_field('cover_image',$post) ?: _wp_field('header_image',$post);
		if (!$item->image && preg_match('/<img[^>]*src="([^"]+)"/i',$item->content,$match)) {
			$item->image = $match[1];
		}
		// ---
		$item->link = (string) _wp_field('cover_link',$post) ?: _wp_field('header_link',$post);
		if (!$item->link) $item->link = (string) ($d = _dictionary($post->post_type.'_more',NULL,'')) ? $d : _dictionary('post_more',NULL,'');
		// ---
		$item->teaser_label = (string) _wp_field('teaser_label',$post) ?: $item->label;
		$item->teaser_headline = (string) _wp_field('teaser_headline',$post) ?: $item->headline;
		$item->teaser_excerpt = (string) _wp_field('teaser_excerpt',$post) ?: $item->excerpt;
		$item->teaser_image = (string) _wp_field('teaser_image',$post) ?: $item->image;
		$item->teaser_link = (string) _wp_field('teaser_link',$post) ?: $item->link;
		// ---
		return $item;
	}

	// ---

	public static function getItemsMeta($items) {
		$meta = new \StdClass();
		$meta->type = [];
		$meta->language = [];
		$meta->category = [];
		$meta->tag = [];
		$meta->author = [];
		foreach ($items AS $item) {
			$meta->type += [$item->type];
			$meta->language += [$item->language];
			$meta->category += $item->category;
			$meta->tag += $item->tag;
			$meta->author += $item->author;
		}
		return $meta;
	}

	// ---

	public static function _wp_categories($post,$terms=[]) {
		if ($get = get_the_category($post->ID)) foreach ($get AS $term) {
			$terms[$term->term_id] = ($d = _dictionary('category_'.preg_replace('/-/','_',$term->slug).'_label',NULL,'')) ? $d : $term->name;
		}
		return $terms;
	}

	public static function _wp_tags($post,$terms=[]) {
		if ($get = get_the_tags($post->ID)) foreach ($get AS $term) {
			$terms[$term->term_id] = ($d = _dictionary('tag_'.preg_replace('/-/','_',$term->slug).'_label',NULL,'')) ? $d : $term->name;
		}
		return $terms;
	}

	public static function _wp_author($post,$author=[]) {
		$author[get_the_author_meta('ID',$post->post_author)] = get_the_author_meta('nickname',$post->post_author);
		return $author;
	}

	// ---

}
