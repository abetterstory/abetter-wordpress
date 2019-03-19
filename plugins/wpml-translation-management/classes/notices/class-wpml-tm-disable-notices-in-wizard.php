<?php

class WPML_TM_Disable_Notices_In_Wizard {

	private $wp_api;

	public function __construct( WPML_WP_API $wp_api ) {
		$this->wp_api = $wp_api;
	}

	public function add_hooks() {
		if ( $this->wp_api->is_tm_page() && ! get_option( WPML_TM_Wizard_Options::WIZARD_COMPLETE_FOR_MANAGER, false ) ) {
			add_action( 'admin_print_scripts', array( $this, 'disable_notices' ) );
		}
	}

	public function disable_notices() {
		global $wp_filter;

		unset( $wp_filter['user_admin_notices'], $wp_filter['admin_notices'], $wp_filter['all_admin_notices'] );
	}
}