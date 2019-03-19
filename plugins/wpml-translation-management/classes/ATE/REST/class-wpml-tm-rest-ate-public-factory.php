<?php

class WPML_TM_REST_ATE_Public_Factory extends WPML_REST_Factory_Loader {

	public function create() {
		$job_actions_factory = new WPML_TM_ATE_Jobs_Actions_Factory();
		$jobs_actions        = $job_actions_factory->create();

		if ( $jobs_actions ) {
			$jobs = new WPML_TM_ATE_Jobs( new WPML_TM_ATE_Job_Records() );
			return new WPML_TM_REST_ATE_Public( $jobs, $jobs_actions, wpml_load_core_tm() );
		}

		return null;
	}
}