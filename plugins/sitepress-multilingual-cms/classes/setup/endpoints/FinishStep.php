<?php

namespace WPML\Setup\Endpoint;

use WPML\Ajax\IHandler;
use WPML\API\Settings;
use WPML\Collect\Support\Collection;
use WPML\FP\Either;
use WPML\LIB\WP\User;
use WPML\TM\ATE\AutoTranslate\Endpoint\EnableATE;
use function WPML\Container\make;
use WPML\FP\Lst;
use WPML\FP\Right;
use WPML\Setup\Option;
use WPML\TM\Menu\TranslationServices\Endpoints\Deactivate;

class FinishStep implements IHandler {

	public function run( Collection $data ) {
		$wpmlInstallation = wpml_get_setup_instance();
		$originalLanguage = Option::getOriginalLang();
		$wpmlInstallation->finish_step1( $originalLanguage );
		$wpmlInstallation->finish_step2( Lst::append( $originalLanguage, Option::getTranslationLangs() ) );
		$wpmlInstallation->finish_installation();

		self::enableFooterLanguageSwitcher();

		$translationMode = Option::getTranslationMode();
		if ( ! Lst::includes( 'users', $translationMode ) ) {
			make( \WPML_Translator_Records::class )->delete_all();
		}

		if ( ! Lst::includes( 'manager', $translationMode ) ) {
			make( \WPML_Translation_Manager_Records::class )->delete_all();
		}

		if ( ! Lst::includes( 'service', $translationMode ) ) {
			make( Deactivate::class )->run( wpml_collect( [] ) );
		}

		if ( Lst::includes( 'myself', $translationMode ) ) {
			self::setCurrentUserToTranslateAllLangs();
		}

		Option::setTranslateEverythingDefault();

		make( EnableATE::class )->run( wpml_collect( [] ) );

		return Right::of( true );
	}

	private static function enableFooterLanguageSwitcher() {
		\WPML_Config::load_config_run();

		/** @var \WPML_LS_Settings $lsSettings */
		$lsSettings = make( \WPML_LS_Dependencies_Factory::class )->settings();

		$settings = $lsSettings->get_settings();
		$settings['statics']['footer']->set( 'show', true );

		$lsSettings->save_settings( $settings );
	}

	private static function setCurrentUserToTranslateAllLangs() {
		$currentUser = User::getCurrent();
		$currentUser->add_cap( \WPML_Translator_Role::CAPABILITY );
		User::updateMeta( $currentUser->ID, \WPML_TM_Wizard_Options::ONLY_I_USER_META, true );

		make( \WPML_Language_Pair_Records::class )->store(
			$currentUser->ID,
			\WPML_All_Language_Pairs::get()
		);
	}
}
