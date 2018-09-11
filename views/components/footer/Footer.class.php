<?php

namespace App\View\Components;

use \ABetter\Wordpress\Menu as WordpressMenu;
use \ABetter\Toolkit\Component as BaseComponent;

class Footer extends BaseComponent {

	// --- Variables

	// --- Parse

	public function parse() {

		$this->brand_url = "/";
		$this->brand_label = _dictionary('brand_label',NULL,_lipsum('word'));
		$this->brand_boilerplate = _dictionary('brand_boilerplate',NULL,_lipsum('medium'));
		$this->brand_logo = _dictionary('brand_logo',NULL,_logosum($this->brand_label));
		$this->brand_logo_svg = (preg_match('/\<svg/',$this->brand_logo)) ? $this->brand_logo : NULL;
		$this->brand_copyright = _dictionary('brand_copyright',NULL,_lipsum('short','2018 @'));

		$this->social = new WordpressMenu('Social');
		$this->social_items = $this->social->items;

	}

}
