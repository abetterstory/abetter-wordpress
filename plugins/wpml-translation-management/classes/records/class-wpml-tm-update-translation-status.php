<?php

class WPML_TM_Update_Translation_Status {

	/**
	 * @param int $job_id
	 * @param int $status
	 */
	public static function by_job_id( $job_id, $status ) {
		$job = wpml_tm_load_job_factory()->get_translation_job( $job_id, false, 0, true );

		if ( (int) $job->get_status_value() !== (int) $status ) {
			wpml_tm_get_records()
				->icl_translation_status_by_translation_id( $job->get_translation_id() )
				->update( array( 'status' => $status ) );
		}
	}
}
