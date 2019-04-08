<?php

namespace ABetter\Toolkit;

use ABetter\Toolkit\Service as BaseService;

class DemoService extends BaseService {

	public function handle() {

		// ...

	}

	// ---

	public function output() {
		$this->data['hello'] = 'world';
	}

}

// ---

echo new DemoService();
