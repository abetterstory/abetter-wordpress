<?php

namespace WPML;

use WPML\FP\Fns;
use WPML\FP\Lst;
use WPML\FP\Obj;
use WPML\FP\Relation;
use WPML\Setup\Option;

class Plugins {
	const WPML_TM_PLUGIN = 'wpml-translation-management/plugin.php';
	const WPML_CORE_PLUGIN = 'sitepress-multilingual-cms/sitepress.php';
	const WPML_SUBSCRIPTION_TYPE_BLOG = 6718;
	const AFTER_INSTALLER = 999;

	public static function loadCoreFirst() {
		$plugins = get_option( 'active_plugins' );

		$isSitePress = function( $value ) {
			return $value === WPML_PLUGIN_BASENAME;
		};

		$newOrder = wpml_collect( $plugins )
			->prioritize( $isSitePress )
			->values()
			->toArray();

		if ( $newOrder !== $plugins ) {
			update_option( 'active_plugins', $newOrder );
		}
	}

	public static function isTMAllowed() {
		$isTMAllowed = true;

		if ( function_exists( 'OTGS_Installer' ) ) {
			$subscriptionType = OTGS_Installer()->get_subscription( 'wpml' )->get_type();
			if ( $subscriptionType && $subscriptionType === self::WPML_SUBSCRIPTION_TYPE_BLOG ) {
				$isTMAllowed = false;
			}
		}

		return $isTMAllowed;
	}

	public static function updateTMAllowedOption() {
		Option::setTMAllowed( self::isTMAllowed() );
	}

	/**
	 * @param bool $isSetupComplete
	 */
	public static function loadEmbeddedTM( $isSetupComplete ) {
		$plugins = is_multisite() && is_network_admin()
			? get_site_option( 'active_sitewide_plugins', array() )
			: get_option( 'active_plugins', array() );
		$tmSlug  = 'wpml-translation-management/plugin.php';

		self::stopPluginActivation( self::WPML_TM_PLUGIN );

		if ( ! self::deactivateTm( $plugins ) ) {
			add_action( "after_plugin_row_$tmSlug", [ self::class, 'showEmbeddedTMNotice' ] );
			add_action( "otgs_installer_clean_plugins_update_cache", [ self::class, 'updateTMAllowedOption' ] );

			$isTMAllowed = Option::isTMAllowed();
			if( $isTMAllowed === null ) {
				add_action( 'after_setup_theme', [ self::class, 'updateTMAllowedOption' ], self::AFTER_INSTALLER );
			}
			if ( ! $isSetupComplete || $isTMAllowed ) {
				if ( defined( 'WPML_TM_PATH' ) ||
					( Relation::propEq( 'action', 'activate-selected', $_POST )
					&& Lst::includes( self::WPML_CORE_PLUGIN, Obj::propOr( [], 'checked', $_POST ) ))
				) {
					// This can happen when a blog has 4.5 active and TM is activate as network plugin, which
					// is possible when 4.5 is only active on a blog but not as network plugin.
					// This will probably never happen, but would cause a fatal error on the blog.
					if ( is_multisite() ) {
						$networkPlugins = get_site_option( 'active_sitewide_plugins', array() );
						self::deactivateTm( $networkPlugins );
					}
					return;
				}
				require_once WPML_PLUGIN_PATH . '/vendor/wpml/tm/plugin.php';
			}
		}
	}

	private static function deactivateTm( $plugins ) {
		if (
			! is_array( $plugins ) ||
			! Lst::includes( self::WPML_TM_PLUGIN, $plugins ) && // 'active_plugins' stores plugins as values.
			! array_key_exists( self::WPML_TM_PLUGIN, $plugins ) // 'active_sitewide_plugins' stores plugins as keys.
		) {
			// TM is not active.
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-includes/pluggable.php';

		deactivate_plugins( self::WPML_TM_PLUGIN );

		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) && wp_redirect( $_SERVER['REQUEST_URI'], 302, 'WPML' ) ) {
			exit;
		}

		return true;
	}

	private static function stopPluginActivation( $pluginSlug ) {
		if ( Relation::propEq( 'action', 'activate', $_GET ) && Relation::propEq( 'plugin', $pluginSlug, $_GET ) ) {
			unset( $_GET['plugin'], $_GET['action'] );
		}

		if (
			Relation::propEq( 'action', 'activate-selected', $_POST )
			&& Lst::includes( $pluginSlug, Obj::propOr( [], 'checked', $_POST ) )
		) {
			$_POST['checked'] = Fns::reject( Relation::equals( $pluginSlug ), $_POST['checked'] );
		}
	}

	public static function showEmbeddedTMNotice() {
		$wpListTable = _get_list_table( 'WP_Plugins_List_Table' );
		?>

		<tr class="plugin-update-tr">
			<td colspan="<?php echo $wpListTable->get_column_count(); ?>" class="plugin-update colspanchange">
				<div class="update-message inline notice notice-error notice-alt">
					<p>
						<?php
						echo _e(
							'Since WPML 4.5.0, the Translation Management plugin is part of WPML core. Please uninstall this plugin and delete it from your plugins folder.',
							'sitepress'
						);
						$readMoreLink = 'https://wpml.org/changelog/2021/09/wpml-4-5-translate-all-of-your-sites-content-with-one-click/';
						?>
						<a href="<?php echo $readMoreLink; ?>" target="_blank" class="wpml-external-link">
							<?php _e( 'Read more', 'sitepress' ); ?>
						</a>
					</p>
				</div>
		</tr>
		<?php
	}
}
