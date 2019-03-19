<?php

class WPML_TM_Disable_Notices_In_Wizard_Factory implements IWPML_Backend_Action_Loader, IWPML_Deferred_Action_Loader {

	public function create() {
		global $sitepress;

		return new WPML_TM_Disable_Notices_In_Wizard( $sitepress->get_wp_api() );
	}

	public function get_load_action() {
		return 'plugins_loaded';
	}
}