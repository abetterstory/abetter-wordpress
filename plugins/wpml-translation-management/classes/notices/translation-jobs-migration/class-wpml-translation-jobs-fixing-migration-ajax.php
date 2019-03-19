<?php

class WPML_Translation_Jobs_Fixing_Migration_Ajax {

	const ACTION                    = 'wpml_translation_jobs_migration';
	const JOBS_MIGRATED_PER_REQUEST = 100;
	const PAGINATION_OPTION         = 'wpml_translation_jobs_migration_processed';

	private $jobs_migration;
	private $jobs_repository;
	private $notice;

	public function __construct(
		WPML_Translation_Jobs_Migration $jobs_migration,
		WPML_Translation_Jobs_Migration_Repository $jobs_repository,
		WPML_Translation_Jobs_Migration_Notice $notice
	) {
		$this->jobs_migration  = $jobs_migration;
		$this->jobs_repository = $jobs_repository;
		$this->notice          = $notice;
	}

	public function run_migration() {
		if ( ! $this->is_valid_request() ) {
			wp_send_json_error();
		}

		$jobs       = $this->jobs_repository->get();
		$total_jobs = count( $jobs );

		$offset = $this->get_already_processed();

		if ( $offset < $total_jobs ) {
			$jobs_chunk = array_slice( $jobs, $offset, self::JOBS_MIGRATED_PER_REQUEST );
			$this->jobs_migration->migrate_jobs( $jobs_chunk, true );

			$done             = $total_jobs <= $offset + self::JOBS_MIGRATED_PER_REQUEST;
			$jobs_chunk_total = count( $jobs_chunk );

			update_option( self::PAGINATION_OPTION, $offset + self::JOBS_MIGRATED_PER_REQUEST );
		} else {
			$done             = true;
			$jobs_chunk_total = 0;
		}

		$result = array(
			'totalJobs'    => $total_jobs,
			'jobsMigrated' => $jobs_chunk_total,
			'done'         => $done,
		);

		if ( $done ) {
			WPML_Translation_Jobs_Migration::mark_all_jobs_migration_as_done();
			$this->notice->remove_notice();
			delete_option( self::PAGINATION_OPTION );
		}

		wp_send_json_success( $result );
	}

	/**
	 * @return bool
	 */
	private function is_valid_request() {
		return wp_verify_nonce( $_POST['nonce'], self::ACTION );
	}

	/**
	 * @return int
	 */
	private function get_already_processed() {
		return (int) get_option( self::PAGINATION_OPTION, 0 );
	}
}