<?php

namespace App\View\Components;

use \ABetter\Wordpress\Menu;
use \ABetter\Wordpress\Index;
use \ABetter\Toolkit\Component as BaseComponent;

class MenuComponent extends BaseComponent {

	// --- Variables

	// --- Build

	public function build() {

		$post = _wp_post();

		$this->front = _wp_page('start');
		$this->front_url = _wp_url($this->front);

		$this->brand_label = get_bloginfo();
		$this->brand_svg = _logosum($this->brand_label);

		$this->search = _wp_page('search');
		$this->search_url = _wp_url($this->search);
		$this->search_label = _dictionary('search_label',NULL,'Search');
		$this->search_placeholder = _dictionary('search_placeholder',NULL,'Search text here…');

		$this->back_label = _dictionary('menu_back',NULL,'Back');

		$this->menu_label = _dictionary('menu_label',NULL,'Menu');
		$this->menu_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><g><line class="line-1" x1="0" y1="20" x2="100" y2="20"></line><line class="line-2" x1="0" y1="50" x2="100" y2="50"></line><line class="line-3" x1="0" y1="80" x2="100" y2="80"></line></g></svg></svg>';

		// ---

		$this->main = Menu::get('main');
		$this->main_items = $this->main->items;

		if (!$this->main_items) {
			$this->main = new Index();
			$this->main_items = $this->main->items;
		}

		// ---

		$this->style = '';

		if (_wp_field('menu_transparent',$post)) $this->style .= ' transparent';

	}

}
