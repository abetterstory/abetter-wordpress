<?php

namespace App\View\Components;

use \ABetter\Toolkit\Component as BaseComponent;

class Demo extends BaseComponent {

	// --- Variables

	public $title;
	public $lead;
	public $body;
	public $image;

	// --- Parse

	public function parse() {

		$this->title = _lipsum('headline','Demo');
		$this->lead = _lipsum('lead','Demo');
		$this->body = _lipsum('body','Demo');
		$this->image = _pixsum('photo:tech');

	}

}
