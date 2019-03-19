<?php

class WPML_Translation_Jobs_Migration_Ajax {

	const ACTION = 'wpml_translation_jobs_migration';

	const JOBS_MIGRATED_PER_REQUEST = 100;

	private $jobs_migration;
	private $jobs_repository;
	private $notice;

	public function __construct( WPML_Translation_Jobs_Migration $jobs_migration, WPML_Translation_Jobs_Migration_Repository $jobs_repository, WPML_Translation_Jobs_Migration_Notice $notice ) {
		$this->jobs_migration            = $jobs_migration;
		$this->jobs_repository           = $jobs_repository;
		$this->notice                    = $notice;
	}

	public function run_migration() {
		if ( ! $this->is_valid_request() ) {
			wp_send_json_error();
		}

		$jobs = $this->jobs_repository->get();

		$jobs_chunk    = array_slice( $jobs, 0, self::JOBS_MIGRATED_PER_REQUEST );
		$this->jobs_migration->migrate_jobs( $jobs_chunk );

		$done = count( $jobs ) === count( $jobs_chunk );
		$total_jobs = count( $jobs );
		$jobs_chunk_total = count( $jobs_chunk );

		$result = array(
			'totalJobs'    => $total_jobs,
			'jobsMigrated' => $jobs_chunk_total,
			'done'         => $done,
		);

		if ( $jobs_chunk_total === $total_jobs ) {
			WPML_Translation_Jobs_Migration::mark_all_jobs_migration_as_done();
			$this->notice->remove_notice();
		}

		wp_send_json_success( $result );
	}

	/**
	 * @return bool
	 */
	private function is_valid_request() {
		return wp_verify_nonce( $_POST['nonce'], self::ACTION );
	}
}