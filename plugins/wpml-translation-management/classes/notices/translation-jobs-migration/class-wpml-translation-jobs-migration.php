<?php

class WPML_Translation_Jobs_Migration {

	const MIGRATION_DONE_KEY          = 'wpml-tm-translation-jobs-migration';
	const ALL_JOBS_MIGRATION_DONE_KEY = 'wpml-tm-all-translation-jobs-migration';
	const MIGRATION_FIX_LOG_KEY       = 'wpml_fixing_migration_log';

	private $jobs_repository;
	private $cms_id_builder;
	private $wpdb;
	private $jobs_api;

	public function __construct(
		WPML_Translation_Jobs_Migration_Repository $jobs_repository,
		WPML_TM_CMS_ID $cms_id_builder,
		wpdb $wpdb,
		WPML_TP_Jobs_API $jobs_api
	) {
		$this->jobs_repository = $jobs_repository;
		$this->cms_id_builder  = $cms_id_builder;
		$this->wpdb            = $wpdb;
		$this->jobs_api        = $jobs_api;
	}

	/**
	 * @param WPML_TM_Post_Job_Entity[] $jobs
	 * @param bool                      $recover_status
	 */
	public function migrate_jobs( array $jobs, $recover_status = false ) {
		$mapped_jobs = $this->map_cms_id_job_id( $jobs );

		if ( $mapped_jobs ) {
			try {
				$tp_jobs = $this->jobs_api->get_jobs_per_cms_ids( array_values( $mapped_jobs ), true );
			} catch ( Exception $e ) {
				$tp_jobs = array();
			}

			foreach ( $jobs as $job ) {
				$cms_id = array_key_exists( $job->get_id(), $mapped_jobs ) ? $mapped_jobs[ $job->get_id() ] : '';
				list( $tp_id, $revision_id ) = $this->get_tp_id_revision_id( $cms_id, $tp_jobs );

				if ( $tp_id !== $job->get_tp_id() ) {
					$new_status = false;
					if ( $recover_status ) {
						$new_status = $this->get_new_status( $job, $tp_id );
						$this->log( $job->get_id(), $job->get_tp_id(), $tp_id, $job->get_status(), $new_status );
					}
					$this->fix_job_fields( $tp_id, $revision_id, $new_status, $job->get_id() );
				}
			}
		}
	}

	/**
	 * @param WPML_TM_Post_Job_Entity $job
	 * @param int $new_tp_id
	 *
	 * @return bool
	 */
	private function get_new_status( WPML_TM_Post_Job_Entity $job, $new_tp_id ) {
		$new_status = false;
		if ( $job->get_tp_id() !== null && $new_tp_id ) {
			if ( $job->get_status() === ICL_TM_NOT_TRANSLATED || $this->has_been_completed_after_release( $job ) ) {
				$new_status = ICL_TM_IN_PROGRESS;
			}
		}

		return $new_status;
	}

	/**
	 * @param WPML_TM_Post_Job_Entity $job
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function has_been_completed_after_release( WPML_TM_Post_Job_Entity $job ) {
		return $job->get_status() === ICL_TM_COMPLETE && $job->get_completed_date() && $job->get_completed_date() > $this->get_4_2_0_release_date();
	}

	/**
	 * @param int $cms_id
	 * @param array $tp_jobs
	 *
	 * @return array
	 */
	private function get_tp_id_revision_id( $cms_id, $tp_jobs ) {
		$tp_id       = 0;
		$revision_id = 0;

		foreach ( $tp_jobs as $tp_job ) {
			if ( $tp_job->cms_id === $cms_id ) {
				$tp_id       = $tp_job->id;
				$revision_id = $tp_job->translation_revision;

				break;
			}
		}

		return array( $tp_id, $revision_id );
	}

	/**
	 * @param int $tp_id
	 * @param int $revision_id
	 * @param int|false $status
	 * @param int $job_id
	 */
	private function fix_job_fields( $tp_id, $revision_id, $status, $job_id ) {
		$new_data = array( 'tp_id' => $tp_id, 'tp_revision' => $revision_id );

		if ( $status ) {
			$new_data['status'] = $status;
		}

		$this->wpdb->update(
			$this->wpdb->prefix . 'icl_translation_status',
			$new_data,
			array( 'rid' => $job_id )
		);
	}

	/**
	 * @param WPML_TM_Post_Job_Entity[] $jobs
	 *
	 * @return array
	 */
	private function map_cms_id_job_id( $jobs ) {
		$mapped_jobs = array();

		foreach ( $jobs as $job ) {
			$cms_id                        = $this->cms_id_builder->cms_id_from_job_id( $job->get_translate_job_id() );
			$mapped_jobs[ $job->get_id() ] = $cms_id;
		}

		return $mapped_jobs;
	}

	/**
	 * @return bool
	 */
	public static function is_migrated() {
		return (bool) get_option( self::MIGRATION_DONE_KEY );
	}

	public static function are_all_jobs_migrated() {
		return (bool) get_option( self::ALL_JOBS_MIGRATION_DONE_KEY );
	}

	public static function mark_migration_as_done() {
		update_option( self::MIGRATION_DONE_KEY, 1 );
	}

	public static function mark_all_jobs_migration_as_done() {
		update_option( self::ALL_JOBS_MIGRATION_DONE_KEY, 1 );
	}


	/**
	 * @return DateTime
	 * @throws Exception
	 */
	private function get_4_2_0_release_date() {
		return new DateTime(
			defined( 'WPML_4_2_0_RELEASE_DATE' ) ? WPML_4_2_0_RELEASE_DATE : '2019-01-20'
		);
	}

	/**
	 * @param int $rid
	 * @param int $old_tp_id
	 * @param int $new_tp_id
	 * @param int $old_status
	 * @param int $new_status
	 */
	private function log( $rid, $old_tp_id, $new_tp_id, $old_status, $new_status ) {
		$log = get_option( self::MIGRATION_FIX_LOG_KEY, array() );
		$key = $new_status ? 'status_changed' : 'status_not_changed';

		$log[ $key ][ $rid ] = array(
			'rid'        => $rid,
			'old_tp_id'  => $old_tp_id,
			'new_tp_id'  => $new_tp_id,
			'old_status' => $old_status,
			'new_status' => $new_status,
		);

		update_option( self::MIGRATION_FIX_LOG_KEY, $log );
	}
}