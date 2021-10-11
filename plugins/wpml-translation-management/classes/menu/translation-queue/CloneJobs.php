<?php

namespace WPML\TM\Menu\TranslationQueue;

use WPML\FP\Obj;
use WPML_Element_Translation_Job;
use WPML_TM_Editors;
use WPML_TM_ATE_Jobs;
use WPML\TM\ATE\JobRecords;
use WPML_TM_ATE_API;

class CloneJobs {
	/**
	 * @var WPML_TM_ATE_Jobs
	 */
	private $ateJobs;

	/**
	 * @var WPML_TM_ATE_API
	 */
	private $apiClient;

	/**
	 * @param WPML_TM_ATE_Jobs $ateJobs
	 * @param WPML_TM_ATE_API  $apiClient
	 */
	public function __construct( WPML_TM_ATE_Jobs $ateJobs, WPML_TM_ATE_API $apiClient ) {
		$this->ateJobs   = $ateJobs;
		$this->apiClient = $apiClient;
	}

	/**
	 * @param int                          $wpmlJobId
	 * @param WPML_Element_Translation_Job $jobObject
	 */
	public function cloneCompletedJob( $wpmlJobId, WPML_Element_Translation_Job $jobObject ) {
		if (
			wpml_tm_load_old_jobs_editor()->get( $wpmlJobId ) === WPML_TM_Editors::ATE
			&& (int) $jobObject->get_status_value() === ICL_TM_COMPLETE
		) {
			$ateJobId = $this->ateJobs->get_ate_job_id( $jobObject->get_id() );
			$result   = $this->apiClient->clone_job( $ateJobId, $jobObject );
			if ( $result ) {
				$this->ateJobs->store( $wpmlJobId, [ JobRecords::FIELD_ATE_JOB_ID => $result['id'] ] );
				$this->ateJobs->set_wpml_status_from_ate( $wpmlJobId, $result['ate_status'] );
			}
		}
	}

	public function maybeCloneWPMLJob( $wpmlJobId ) {
		$jobsEditor = wpml_tm_load_old_jobs_editor();
		if (
			\WPML_TM_ATE_Status::is_enabled_and_activated() &&
			$jobsEditor->get( $wpmlJobId ) === WPML_TM_Editors::ATE &&
			! $this->ateJobs->get_ate_job_id( $wpmlJobId )
		) {
			$rid = wpml_tm_get_records()->icl_translate_job_by_job_id( $wpmlJobId )->rid();

			$params = json_decode( wp_json_encode( [
				'jobs' => [ wpml_tm_create_ATE_job_creation_model( $wpmlJobId, $rid ) ]
			] ), true );

			$response = $this->apiClient->create_jobs( $params );

			if ( ! is_wp_error( $response ) && Obj::prop( 'jobs', $response ) ) {
				$this->ateJobs->store( $wpmlJobId, [ JobRecords::FIELD_ATE_JOB_ID => Obj::path( [ 'jobs', $rid ], $response ) ] );
				$jobsEditor->set( $wpmlJobId, WPML_TM_Editors::ATE );
				$this->ateJobs->warm_cache( [ $wpmlJobId ] );
			}
		}
	}
}
