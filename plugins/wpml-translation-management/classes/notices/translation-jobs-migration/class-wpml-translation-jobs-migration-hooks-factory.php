<?php

class WPML_Translation_Jobs_Migration_Hooks_Factory implements IWPML_Backend_Action_Loader, IWPML_AJAX_Action_Loader {

	/**
	 * It creates an instance of WPML_Translation_Jobs_Migration_Notice.
	 *
	 * @return null|WPML_Translation_Jobs_Migration_Hooks
	 */
	public function create() {
		$migrate_all_jobs = false;

		$wpml_notices = wpml_get_admin_notices();
		$wpml_notices->remove_notice( WPML_Translation_Jobs_Migration_Notice::NOTICE_GROUP_ID, 'all-translation-jobs-migration' );
		$wpml_notices->remove_notice( WPML_Translation_Jobs_Migration_Notice::NOTICE_GROUP_ID, 'translation-jobs-migration' );


		if ( WPML_Translation_Jobs_Migration::is_migrated() ) {

			if ( ! WPML_Translation_Jobs_Migration::are_all_jobs_migrated() ) {
				$migrate_all_jobs = true;
			} else {
				return null;
			}
		}

		$template_service = new WPML_Twig_Template_Loader( array( WPML_TM_PATH . '/templates/translation-jobs-migration/' ) );

		if ( $migrate_all_jobs ) {
			$notice = new WPML_All_Translation_Jobs_Migration_Notice( $wpml_notices, $template_service->get_template() );
		} else {
			$notice = new WPML_Translation_Jobs_Missing_TP_ID_Migration_Notice( $wpml_notices, $template_service->get_template() );
		}

		$jobs_migration_repository = new WPML_Translation_Jobs_Migration_Repository( wpml_tm_get_jobs_repository(), $migrate_all_jobs );

		global $wpml_post_translations, $wpml_term_translations, $wpdb;

		$job_factory     = wpml_tm_load_job_factory();
		$wpml_tm_records = new WPML_TM_Records( $wpdb, $wpml_post_translations, $wpml_term_translations );
		$cms_id_helper   = new WPML_TM_CMS_ID( $wpml_tm_records, $job_factory );
		$jobs_migration  = new WPML_Translation_Jobs_Migration( $jobs_migration_repository, $cms_id_helper, $wpdb, wpml_tm_get_tp_jobs_api() );
		if ( $migrate_all_jobs ) {
			$ajax_handler = new WPML_Translation_Jobs_Fixing_Migration_Ajax(
				$jobs_migration,
				$jobs_migration_repository,
				$notice
			);
		} else {
			$ajax_handler = new WPML_Translation_Jobs_Migration_Ajax(
				$jobs_migration,
				$jobs_migration_repository,
				$notice
			);
		}

		return new WPML_Translation_Jobs_Migration_Hooks( $notice, $ajax_handler, $jobs_migration_repository, wpml_get_upgrade_schema() );
	}
}
