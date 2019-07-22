<?php

namespace App\View\Components;

use \ABetter\Wordpress\Menu;
use \ABetter\Wordpress\Index;
use \ABetter\Toolkit\Component as BaseComponent;

class FooterComponent extends BaseComponent {

	// --- Variables

	// --- Build

	public function build() {

		$this->front = _wp_page('start');
		$this->front_url = _wp_url($this->front);

		$this->brand_label = _wp_bloginfo();
		$this->brand_svg = _logosum($this->brand_label);
		$this->brand_boilerplate = _dictionary('brand_boilerplate',NULL,_lipsum('medium'));
		$this->brand_copyright = _dictionary('brand_copyright',NULL,_lipsum('short','2019 @'));

	}

}
