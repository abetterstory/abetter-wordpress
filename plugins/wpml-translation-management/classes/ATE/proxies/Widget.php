<?php
namespace WPML\ATE\Proxies;

class Widget implements \IWPML_Frontend_Action, \IWPML_DIC_Action {
	const ENDPOINT_ATE_WIDGET_SCRIPT  = 'ate/widget/script';
	const QUERY_VAR_ATE_WIDGET_SCRIPT = 'wpml-app';
	const SCRIPT_NAME                 = 'ate-widget';
	const PRIORITY_LATE               = 200;

	public function add_hooks() {
		add_filter(
			'template_include',
			function ( $template ) {
				if ( current_user_can( \WPML_Manage_Translations_Role::CAPABILITY ) || current_user_can( 'manage_options' ) ) {
					$app = filter_input( INPUT_GET, self::QUERY_VAR_ATE_WIDGET_SCRIPT, FILTER_SANITIZE_STRING );

					if ( self::SCRIPT_NAME === $app ) {
						$script = WPML_TM_PATH . '/res/js/' . $app . '.php';

						if ( file_exists( $script ) ) {
							return $script;
						}
					}
				}

				return $template;
			},
			self::PRIORITY_LATE
		);
	}
}
