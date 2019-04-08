<?php

namespace App\View\Components;

use \ABetter\Wordpress\Menu as WordpressMenu;
use \ABetter\Toolkit\Component as BaseComponent;

class Menu extends BaseComponent {

	// --- Variables

	public $mobile_label;

	public $brand_url;
	public $brand_label;
	public $brand_image;

	public $search_url;
	public $search_label;
	public $search_placeholder;

	public $main_items;
	public $language_items;

	// --- Build

	public function build() {

		$this->mobile_label = _dictionary('menu_label');

		$this->brand_url = "/";
		$this->brand_label = _dictionary('brand_label',NULL,_lipsum('word'));
		$this->brand_logo = _dictionary('brand_logo',NULL,_logosum($this->brand_label));
		$this->brand_logo_svg = (preg_match('/\<svg/',$this->brand_logo)) ? $this->brand_logo : NULL;

		$this->search_url = "/search/";
		$this->search_label = _dictionary('search_label');
		$this->search_placeholder = _dictionary('search_placeholder');

		$this->main = new WordpressMenu('Main');
		$this->main_items = $this->main->items;

		$this->language = new WordpressMenu('Language');
		$this->language_items = $this->language->items;

		// ---

		foreach ($this->main_items AS &$item) {
			if (preg_match('/is-icon/',$item->style)) {
				$item->icon = $item->style;
				$item->style = preg_replace('/fa[^ ]*/',"",$item->style);
			}
		}
		unset($item);

		foreach ($this->language_items AS &$item) {
			if (preg_match('/is-icon/',$item->style)) {
				$item->icon = $item->style;
				$item->style = preg_replace('/fa[^ ]*/',"",$item->style);
			}
		}
		unset($item);

	}

}
