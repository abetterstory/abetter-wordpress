<?php

namespace ABetter\Wordpress;

class Menu {

	public $scope;

	public $id;
	public $items;
	public $breadcrumbs;

	public static $menu;

	// --- Constructor

	public function __construct($defined_vars = []) {
		$defined_vars = (is_string($defined_vars)) ? ['id' => $defined_vars] : $defined_vars;
		$this->scope = (object) $defined_vars;
		if (!empty($this->scope->id)) {
			$this->id = $this->scope->id;
			$this->items = self::getItems($this->id);
			$this->breadcrumbs = self::getBreadcrumbs($this->id);
		}
	}

	// ---

	public static function get($id,$props=NULL) {
		if (isset(self::$menu[$id])) return self::$menu[$id];
		self::build($id,$props);
		return self::$menu[$id];
	}

	public static function getItems($id) {
		if (isset(self::$menu[$id]->items)) return self::$menu[$id]->items;
		self::build($id);
		return self::$menu[$id]->items;
	}

	public static function getBreadcrumbs($id) {
		if (isset(self::$menu[$id]->breadcrumbs)) return self::$menu[$id]->breadcrumbs;
		self::build($id);
		return self::$menu[$id]->breadcrumbs;
	}

	// ---

	public static function getItem($id,$find) {
		$menu = &self::$menu[$id];
		foreach ($menu->items AS $id => $item) {
			if ($id == $find || $item->page_id == $find) return $item;
			if ($item->items) foreach ($item->items AS $id => $item) {
				if ($id == $find || $item->page_id == $find) return $item;
				if ($item->items) foreach ($item->items AS $id => $item) {
					if ($id == $find || $item->page_id == $find) return $item;
					if ($item->items) foreach ($item->items AS $id => $item) {
						if ($id == $find || $item->page_id == $find) return $item;
					}
				}
			}
		}
	}

	// ---

	public static function build($id,$props=NULL) {
		Controller::loadWp();
		if (empty(self::$menu[$id])) self::$menu[$id] = new \StdClass();
		$menu = &self::$menu[$id];
		$menu->slug = (string) $id;
		$menu->props = ($props) ? (object) $props : NULL;
		$menu->term = ($t = get_term_by('slug',$menu->slug,'nav_menu')) ? $t : ((isset(get_nav_menu_locations()[$menu->slug])) ? get_term(get_nav_menu_locations()[$menu->slug],'nav_menu') : NULL);
		$menu->menu = (isset($menu->term->term_id)) ? wp_get_nav_menu_items($menu->term->term_id) : array();
		$menu->terms = array();
		$menu->current = (object) ['title' => NULL, 'url' => self::getUrl(), 'page_id' => self::getId(), 'term_id' => NULL];
		$menu->items = self::buildItems($id);
		$menu->breadcrumbs = self::buildBreadcrumbs($id);
	}

	public static function buildItems($id) {
		$menu = &self::$menu[$id];
		$items = array();
		$delete = array();
		foreach ($menu->menu AS $term) { // Pass 1 : Parse
			$item = new \StdClass();
			$item->id = (int) $term->ID;
			$item->term = $term;
			$item->term_id = (int) $term->ID;
			$item->page = self::getPage($term->object_id);
			$item->page_id = $item->page->ID;
			$item->title = (string) self::getTitle($item->page);
			$item->url = ($term->type == 'custom') ? (string) $term->url : (string) self::getUrl($item->page);
			$item->label = (string) $term->title;
			$item->description = (string) $term->description;
			$item->order = (int) $term->menu_order;
			$item->parent = (int) $term->menu_item_parent;
			$item->style = (string) implode($term->classes," ");
			$item->current = (string) self::isCurrent($item->url,'current');
			$item->front = (string) self::isFront($item->url,'front');
			$item->items = array();
			$items[$item->id] = $item;
			$menu->terms[$item->id] = $item;
			if ($item->current) {
				$menu->current->title = $item->title;
				$menu->current->term_id = $item->id;
			}
		}
		foreach ($items AS $item) { // Pass 2 : Hierarchy
			if ($item->parent && isset($items[$item->parent])) {
				$items[$item->parent]->items[$item->id] = $item;
				$delete[] = $item->id;
			}
		}
		foreach ($delete AS $id) unset($items[(string)$id]); // Pass 3 : Cleanup
		return $items;
	}

	public static function buildBreadcrumbs($id) {
		$menu = &self::$menu[$id];
		$breadcrumbs = array();
		$breadcrumbs[] = $menu->current;
		$next = (isset($menu->terms[$menu->current->term_id])) ? $menu->terms[$menu->current->term_id] : NULL;
		while (!empty($next->parent)) {
			$next = $menu->terms[$next->parent];
			$breadcrumbs[] = (object) ['title' => $next->title, 'url' => $next->url, 'term_id' => $next->term_id, 'page_id' => $next->page_id];
		}
		return array_reverse($breadcrumbs);
	}

	// ---

	public static function getPage($page=NULL) {
		if ($page === NULL) { $page = \ABetter\Wordpress\Post::$post; };
		if (empty($page->ID)) {
			if (is_numeric($page)) {
				$page = get_page($page);
			} else if (is_string($page)) {
				$page = get_page_by_path($page);
			}
		}
		return $page;
	}

	public static function getId($page=NULL) {
		$page = self::getPage($page);
		return (isset($page->ID)) ? $page->ID : "";
	}

	public static function getUrl($page=NULL) {
		$page = self::getPage($page);
		return (isset($page->ID)) ? urldecode(self::makeRelative(get_permalink($page->ID))) : "";
	}

	public static function getTitle($page=NULL) {
		$page = self::getPage($page);
		return (isset($page->post_title)) ? $page->post_title : "";
	}

	// ---

	public static function makeRelative($url) {
		$rel = parse_url($url,PHP_URL_PATH);
		$rel .= (($q = parse_url($url,PHP_URL_QUERY)) ? "?{$q}" : "");
		$rel .= (($f = parse_url($url,PHP_URL_FRAGMENT)) ? "#{$f}" : "");
		return $rel;
	}

	public static function isCurrent($url, $class='current') {
		if ($item_hash = parse_url($url,PHP_URL_FRAGMENT)) return '';
		$item_path = urldecode(parse_url($url,PHP_URL_PATH));
		$current_path = urldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
		$current_path = preg_replace('/\/$/',"",$current_path) . '/'; // Fix for laravel removing trailing slash
		return ($item_path == $current_path) ? $class : '';
	}

	public static function isFront($url, $class='front') {
		return ($url == '/') ? $class : '';
	}

	// ---

}
