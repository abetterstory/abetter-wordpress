<?php

namespace ABetter\Wordpress;

class Menu {

	public $scope;

	public $id;
	public $items;
	public $breadcrumbs;
	public $label;

	public static $languages;
	public static $language;
	public static $language_default;
	public static $language_request;

	public static $translation;
	public static $translations;

	public static $menu;

	// --- Constructor

	public function __construct($defined_vars = []) {
		$defined_vars = (is_string($defined_vars)) ? ['id' => $defined_vars] : $defined_vars;
		$this->scope = (object) $defined_vars;
		Controller::loadWp();
		if (!empty($this->scope->id)) {
			$this->id = $this->scope->id;
			$this->items = self::getItems($this->id);
			$this->breadcrumbs = self::getBreadcrumbs($this->id);
			$this->label = self::getLabel($this->id);
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

	public static function getLabel($id) {
		if (isset(self::$menu[$id]->label)) return self::$menu[$id]->label;
		self::build($id);
		return self::$menu[$id]->label;
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
		if (empty(self::$menu[$id])) self::$menu[$id] = new \StdClass();
		$menu = &self::$menu[$id];
		$menu->slug = strtolower($id);
		$menu->props = ($props) ? (object) $props : NULL;
		$menu->term = ($t = get_term_by('slug',$menu->slug,'nav_menu')) ? $t : ((isset(get_nav_menu_locations()[$menu->slug])) ? get_term(get_nav_menu_locations()[$menu->slug],'nav_menu') : NULL);
		$menu->term = self::getTranslated($menu->term);
		$menu->language = self::getLanguage($menu->term);
		$menu->menu = (isset($menu->term->term_id)) ? wp_get_nav_menu_items($menu->term->term_id) : array();
		$menu->terms = array();
		$menu->current = (object) [];
		$menu->items = self::buildItems($id);
		$menu->breadcrumbs = self::buildBreadcrumbs($id);
		$menu->name = (isset($menu->term->slug)) ? $menu->term->slug : NULL;
		$menu->label = (isset($menu->term->name)) ? $menu->term->name : NULL;
	}

	public static function buildItems($id) {
		$menu = &self::$menu[$id];
		$items = array();
		$delete = array();
		foreach ($menu->menu AS $term) { // Pass 1 : Parse
			$item = self::buildTerm($term);
			if ($item->page_status != 'publish') continue;
			$items[$item->id] = $item;
			$menu->terms[$item->id] = $item;
			if ($item->current) $menu->current = $item;
		}
		foreach ($items AS $item) { // Pass 2 : Hierarchy
			if ($item->current && isset($items[$item->parent]) && !preg_match('/not-current/',$item->style)) {
				$items[$item->parent]->current .= ' current-child';
			}
			if ($item->parent && isset($items[$item->parent])) {
				$items[$item->parent]->items[$item->id] = $item;
				$delete[] = $item->id;
			}
		}
		foreach ($delete AS $id) unset($items[(string)$id]); // Pass 3 : Cleanup
		return $items;
	}

	public static function buildTerm($term) {
		$item = new \StdClass();
		$item->id = (int) $term->ID;
		$item->term = $term;
		$item->term_id = (int) $term->ID;
		$item->page = self::getPage($term->object_id);
		$item->page_id = $item->page->ID;
		$item->page_status = $item->page->post_status;
		$item->title = (string) self::getTitle($item->page);
		$item->label = (string) (!empty($term->post_title)) ? htmlspecialchars_decode($term->post_title) : $item->title;
		$item->url = ($term->type == 'custom') ? (string) $term->url : (string) self::getUrl($item->page);
		$item->description = (string) $term->description;
		$item->order = (int) $term->menu_order;
		$item->target = (string) $term->target;
		$item->parent = (int) $term->menu_item_parent;
		$item->style = (string) implode($term->classes," ");
		$item->current = (string) _is_current($item->url,'current');
		$item->front = Post::isFront($item->page);
		$item->l10n = Post::getL10n($item->page);
		$item->language = $item->l10n->language;
		$item->items = array();
		return $item;
	}

	public static function buildBreadcrumbs($id) {
		$menu = &self::$menu[$id];
		$breadcrumbs = array();
		if (!empty($menu->current->term_id)) $breadcrumbs[] = $menu->current;
		$next = (isset($menu->current->term_id) && isset($menu->terms[$menu->current->term_id])) ? $menu->terms[$menu->current->term_id] : NULL;
		while (!empty($next->parent)) {
			$next = $menu->terms[$next->parent];
			$breadcrumbs[] = self::buildBreadcrumb($next->page);
		}
		return array_reverse($breadcrumbs);
	}

	public static function buildBreadcrumb($page) {
		$item = new \StdClass();
		$item->id = (int) $page->ID;
		$item->title = (string) self::getTitle($page);
		$item->url = (string) self::getUrl($page);
		return $item;
	}

	// ---

	public static function getPage($page=NULL) {
		if ($page === NULL) $page = Post::$post;
		if (empty($page->ID)) {
			if (is_numeric($page)) {
				$page = get_page($page);
			} else if (is_string($page)) {
				$page = get_page_by_path($page);
			}
		}
		$page = self::translatePage($page);
		return $page;
	}

	// ---

	public static function getTranslated($term) {
		if (!function_exists('icl_object_id') || self::getRequestLanguage() == self::getDefaultLanguage()) return $term;
		if ($translation = self::getTranslation($term,self::getRequestLanguage())) return get_term($translation);
		return $term;
	}

	public static function getDefaultLanguage() {
		if (!empty(self::$language_default)) return self::$language_default;
		if (function_exists('icl_object_id')) {
			global $sitepress;
			self::$language_default = $sitepress->get_default_language();
		} else {
			self::$language_default = strtolower(strtok(get_bloginfo('language'),'-'));
		}
		return self::$language_default;
	}

	public static function getRequestLanguage() {
		if (!empty(self::$language_request)) return self::$language_request;
		if (function_exists('icl_object_id')) {
			self::$language_request = ICL_LANGUAGE_CODE;
		} else {
			self::$language_request = self::getDefaultLanguage();
		}
		return self::$language_request;
	}

	public static function getLanguage($term) {
		$language = self::getDefaultLanguage();
 		if (function_exists('icl_object_id')) {
 			$language = ($lc = $GLOBALS['wpdb']->get_var("SELECT language_code FROM wp_icl_translations WHERE (element_id = \"{$term->term_id}\" AND element_type = \"tax_{$term->taxonomy}\")")) ? $lc : $language;
 		}
		return $language;
	}

	public static function getTranslations($term) {
		$translations = [];
		if (function_exists('icl_object_id')) {
			$trid = $GLOBALS['wpdb']->get_var("SELECT trid FROM wp_icl_translations WHERE (element_id = \"{$term->term_id}\" AND element_type = \"tax_{$term->taxonomy}\")");
			$results = ($tr = $GLOBALS['wpdb']->get_results("SELECT language_code,element_id FROM wp_icl_translations WHERE (trid = \"{$trid}\" AND element_type = \"tax_{$term->taxonomy}\")",ARRAY_N)) ? $tr : $translations;
			foreach ($results AS $row) $translations[(string)$row[0]] = (integer)$row[1];
		}
		return $translations;
	}

	public static function getTranslation($term,$language) {
		if (!$translations = self::getTranslations($term)) return NULL;
		foreach ($translations AS $lc => $id) {
			if ($lc == $language) return $id;
		}
		return NULL;
	}

 	// ?

	public static function translatePage($page,$language=NULL,$fallback=TRUE) {
		if (($request = Post::getRequestLanguage($page)) != ($current = Post::getLanguage($page))) {
			if ($id = Post::getTranslation($page,$request)) {
				$page = get_post($id);
			}
		}
		return $page;
	}

	// --

	public static function getId($page=NULL) {
		$page = self::getPage($page);
		return (isset($page->ID)) ? $page->ID : "";
	}

	public static function getUrl($page=NULL) {
		$page = self::getPage($page);
		return (isset($page->ID)) ? urldecode(_relative(get_permalink($page->ID))) : "";
	}

	public static function getTitle($page=NULL) {
		$page = self::getPage($page);
		return (isset($page->post_title)) ? $page->post_title : "";
	}

	// ---

}
