<?php

use function WPML\Container\make;

class WPML_Archives_Query_Factory implements IWPML_Frontend_Action_Loader {

	public function create() {
		return make( WPML_Archives_Query::class );
	}
}
