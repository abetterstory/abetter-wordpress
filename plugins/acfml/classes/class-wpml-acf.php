<?php

/**
 * Class WPML_ACF
 */
class WPML_ACF {

	private $WPML_ACF_Requirements;
	private $WPML_ACF_Editor_Hooks;

	/**
	 * @return WPML_ACF_Worker
	 */
	public function init_worker() {
		if ( $this->is_acf_active() ) {
			global $wpdb;
			add_action( 'wpml_loaded', array( $this, 'init_acf_xliff' ) );
			add_action( 'wpml_loaded', array( $this, 'init_acf_pro' ) );
			add_action( 'wpml_loaded', array( $this, 'init_acf_field_annotations' ) );
			add_action( 'wpml_loaded', array( $this, 'init_custom_fields_synchronisation_handler'));
			add_action( 'wpml_loaded', array( $this, 'init_acf_location_rules' ) );
			add_action( 'wpml_loaded', array( $this, 'init_acf_attachments' ) );
			add_action( 'wpml_loaded', array( $this, 'init_acf_field_settings' ) );

			$this->WPML_ACF_Requirements = new WPML_ACF_Requirements();

			$this->WPML_ACF_Editor_Hooks = new WPML_ACF_Editor_Hooks();
			$this->WPML_ACF_Editor_Hooks->init_hooks();

			$this->WPML_ACF_Display_Translated = new WPML_ACF_Display_Translated();

			return $this->init_duplicated_post( $wpdb );
		}
	}

	private function is_acf_active() {
		$active = false;

		$active_plugins = get_option('active_plugins');

		if ( is_array( $active_plugins ) ) {
			foreach ( $active_plugins as $plugin ) {
				if ( stristr( $plugin, '/acf.php' ) ) {
					$active = true;
					break;
				}
			}
		}

		return $active;

	}

	private function init_duplicated_post( $wpdb ) {
		$duplicated_post = new WPML_ACF_Duplicated_Post( $wpdb );

		return new WPML_ACF_Worker( $duplicated_post );
	}

	public function init_acf_xliff() {
		if ( defined( 'WPML_ACF_XLIFF_SUPPORT' ) && WPML_ACF_XLIFF_SUPPORT ) {
			if ( is_admin() ) {
				if ( class_exists( 'acf' ) ) {
					global $wpdb, $sitepress;
					$WPML_ACF_Xliff = new WPML_ACF_Xliff( $wpdb, $sitepress );
					$WPML_ACF_Xliff->init_hooks();
				}
			}
		}
	}

	public function init_acf_pro() {
		$WPML_ACF_Pro = new WPML_ACF_Pro();
	}

	public function init_acf_field_annotations() {
		$WPML_ACF_Options_Page = new WPML_ACF_Options_Page();
		$WPML_ACF_Field_Annotations = new WPML_ACF_Field_Annotations( $WPML_ACF_Options_Page );
	}

	public function init_custom_fields_synchronisation_handler() {
		$WPML_ACF_Custom_Fields_Sync = new WPML_ACF_Custom_Fields_Sync();
		$WPML_ACF_Custom_Fields_Sync->register_hooks();
	}
	
	public function init_acf_location_rules() {
			$WPML_ACF_Location_Rules = new WPML_ACF_Location_Rules();
	}

	public function init_acf_attachments() {
		$WPML_ACF_Attachments = new WPML_ACF_Attachments();
		$WPML_ACF_Attachments->register_hooks();
	}

	public function init_acf_field_settings() {
		global $iclTranslationManagement;
		$WPML_ACF_Field_Settings = new WPML_ACF_Field_Settings( $iclTranslationManagement );
	}
}
